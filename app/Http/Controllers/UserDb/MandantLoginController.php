<?php
/**
 * FILE:        app/Http/Controllers/UserDb/MandantLoginController.php
 * VERSION:     1.9.0
 * AUTOR:       Martin Wagner
 * DATUM:       2026-06-04
 *
 * ZWECK:       Mand-Login mit 2FA und Passkey — Formular anzeigen, Credentials prüfen,
 *              2FA-Code verifizieren, Passkey-Options liefern, Passkey-Assertion prüfen,
 *              Session schreiben, Logout.
 *
 * FUNCTIONS:   showLogin()       — Zeigt das Mandant-Login-Formular an.
 *                                  Reads: —
 *              handleLogin()     — Validiert mand_email + password; sucht MandUser
 *                                  per mand_email; prüft Hash; erzeugt 2FA-Code via
 *                                  TwofaService::generate(); sendet Code per
 *                                  TwoFactorCodeMail; speichert pending_mand_id in
 *                                  Session; leitet zu mandant.login.2fa.
 *                                  Reads: userdb.mand_user.mand_id, mand_email,
 *                                         mand_pw_hash, mand_firstname
 *              showTwoFactor()   — Prüft pending_mand_id in Session;
 *                                  zeigt mandant.auth.two-factor View an.
 *                                  Reads: —
 *              verifyTwoFactor() — Validiert tfa_code (digits:6); liest
 *                                  pending_mand_id aus Session; delegiert Prüfung
 *                                  an TwofaService::verify(); bei Erfolg: Session
 *                                  regenerieren, _user_type und _mand_id schreiben,
 *                                  _prompt_passkey setzen, pending_mand_id löschen,
 *                                  Redirect zu mandant.dashboard.
 *                                  Reads: sessiondb.twofa_code.* (via TwofaService)
 *              passkeyOptions()  — Erstellt userless PublicKeyCredentialRequestOptions
 *                                  (discoverable credential flow), speichert Challenge
 *                                  in Session, gibt JSON zurück.
 *                                  Reads: —
 *              passkeyLogin()    — Verifiziert Passkey-Assertion; sucht Passkey per
 *                                  credential_id; aktualisiert sign_count + last_used_at;
 *                                  baut Session auf; setzt _prompt_passkey = false;
 *                                  gibt JSON mit redirect zurück.
 *                                  Reads:  userdb.passkey.credential_id, public_key,
 *                                          user_type, user_id, sign_count
 *                                          userdb.mand_user.mand_id
 *                                  Writes: userdb.passkey.sign_count, last_used_at
 *              logout()          — Invalidiert die Session und leitet zu mandant.login
 *                                  mit status-Flash 'Sie wurden erfolgreich abgemeldet.'
 *                                  Reads: —
 *
 * CALLS:       App\Models\UserDb\MandUser::where()->first()
 *              App\Models\UserDb\MandUser::find()
 *              App\Models\UserDb\Passkey::where()->first()
 *              App\Mail\TwoFactorCodeMail
 *              App\Services\SessionDb\TwofaService::generate()
 *              App\Services\SessionDb\TwofaService::verify()
 *              App\Services\SessionDb\SessionIntegrityService::buildSessionData()
 *              App\Services\Passkey\PasskeyRepository::findOneByCredentialId()
 *              App\Services\Passkey\PasskeySessionStorage::store()
 *              App\Services\Passkey\PasskeySessionStorage::get()
 *              App\Services\Passkey\PasskeySessionStorage::clear()
 *              Webauthn\AuthenticatorAssertionResponseValidator::check()
 *              Webauthn\CeremonyStep\CeremonyStepManagerFactory::requestCeremony()
 *              Illuminate\Support\Facades\Hash::check()
 *              Illuminate\Support\Facades\Mail::to()->send()
 *              Illuminate\Support\Facades\DB::connection('sessiondb')->table()->delete()
 *
 * DB ACCESS:   userdb.mand_user.mand_id, mand_email, mand_pw_hash, mand_firstname
 *              userdb.passkey.credential_id, public_key, user_type, user_id,
 *              sign_count, last_used_at
 *              sessiondb.twofa_code.* (via TwofaService)
 *              sessiondb.session.expires_at (DELETE bei Login)
 */

namespace App\Http\Controllers\UserDb;

use App\Mail\TwoFactorCodeMail;
use App\Models\UserDb\MandUser;
use App\Models\UserDb\Passkey;
use App\Services\Passkey\PasskeyRepository;
use App\Services\Passkey\PasskeySessionStorage;
use App\Services\SessionDb\SessionIntegrityService;
use App\Services\SessionDb\TwofaService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use ParagonIE\ConstantTime\Base64UrlSafe;
use Symfony\Component\Serializer\SerializerInterface;
use Throwable;
use Webauthn\AttestationStatement\AttestationStatementSupportManager;
use Webauthn\AuthenticatorAssertionResponse;
use Webauthn\AuthenticatorAssertionResponseValidator;
use Webauthn\CeremonyStep\CeremonyStepManagerFactory;
use Webauthn\Denormalizer\WebauthnSerializerFactory;
use Webauthn\PublicKeyCredential;
use Webauthn\PublicKeyCredentialRequestOptions;

class MandantLoginController extends UserDbController
{
    private SerializerInterface $serializer;

    public function __construct(
        private readonly TwofaService             $twofaService,
        private readonly SessionIntegrityService   $sessionIntegrityService,
        private readonly PasskeyRepository         $passkeyRepository,
        private readonly PasskeySessionStorage     $passkeySessionStorage,
    ) {
        $this->serializer = (new WebauthnSerializerFactory(
            AttestationStatementSupportManager::create()
        ))->create();
    }

    public function showLogin(): View
    {
        return view('auth.login-modal');
    }

    public function handleLogin(Request $request): RedirectResponse
    {
        $request->validate([
            'mand_email' => ['required', 'email'],
            'password'   => ['required', 'string'],
        ]);

        $mand = MandUser::where('mand_email', $request->mand_email)->first();

        if (! $mand || ! Hash::check($request->password, $mand->mand_pw_hash)) {
            return back()
                ->withErrors(['credentials' => 'Diese Zugangsdaten sind uns nicht bekannt.'])
                ->withInput(['mand_email' => $request->mand_email])
                ->with('login_page', 'mand');
        }

        $code = $this->twofaService->generate('mand', $mand->mand_id, 'login');

        Mail::to($mand->mand_email)
            ->send(new TwoFactorCodeMail($code, $mand->mand_firstname ?? ''));

        $request->session()->put('pending_mand_id', $mand->mand_id);

        return redirect()->route('mandant.login.2fa');
    }

    public function showTwoFactor(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('pending_mand_id')) {
            return redirect()->route('mandant.login');
        }

        return view('mandant.auth.two-factor');
    }

    public function verifyTwoFactor(Request $request): RedirectResponse
    {
        $request->validate([
            'tfa_code' => ['required', 'digits:6'],
        ]);

        $mandId = $request->session()->get('pending_mand_id');

        if (! $mandId) {
            return redirect()->route('mandant.login');
        }

        $verified = $this->twofaService->verify(
            'mand',
            (int) $mandId,
            'login',
            $request->string('tfa_code')->toString()
        );

        if (! $verified) {
            return back()->withErrors(['tfa_code' => 'Ungültiger oder abgelaufener Code.']);
        }

        $sessionData = $this->sessionIntegrityService->buildSessionData('mand', (int) $mandId);

        $request->session()->regenerate();
        $request->session()->put('_user_type', $sessionData['user_type']);
        $request->session()->put('_mand_id',   $sessionData['mand_id']);
        $request->session()->forget('pending_mand_id');

        $hasPasskey = Passkey::where('user_type', 'mand')
            ->where('user_id', $mandId)
            ->exists();
        $request->session()->put('_prompt_passkey', !$hasPasskey);

        DB::connection('sessiondb')
            ->table('session')
            ->where('expires_at', '<', now())
            ->delete();

        return redirect()->route('mandant.dashboard');
    }

    /**
     * Liefert PublicKeyCredentialRequestOptions für den userless/discoverable flow.
     * Kein allowCredentials-Array — der Authenticator wählt selbst den passenden Key.
     */
    public function passkeyOptions(Request $request): JsonResponse
    {
        $options = PublicKeyCredentialRequestOptions::create(
            challenge:        random_bytes(32),
            rpId:             parse_url(config('app.url'), PHP_URL_HOST),
            allowCredentials: [],
            userVerification: PublicKeyCredentialRequestOptions::USER_VERIFICATION_REQUIREMENT_REQUIRED,
            timeout:          60000,
        );

        $this->passkeySessionStorage->store($request, $options);

        return response()->json(
            json_decode($this->serializer->serialize($options, 'json'), true)
        );
    }

    /**
     * Verifiziert eine Passkey-Assertion und baut die Session auf.
     * Discoverable credential flow: user_type + user_id kommen aus dem Passkey-Datensatz.
     */
    public function passkeyLogin(Request $request): JsonResponse
    {
        $requestOptions = $this->passkeySessionStorage->get($request);
        if (! $requestOptions instanceof PublicKeyCredentialRequestOptions) {
            return response()->json([
                'success' => false,
                'message' => 'Keine aktive Challenge. Bitte die Seite neu laden.',
            ]);
        }

        try {
            $credentialJson = json_encode(
                $request->only(['id', 'rawId', 'type', 'response'])
            );

            /** @var PublicKeyCredential $publicKeyCredential */
            $publicKeyCredential = $this->serializer->deserialize(
                $credentialJson,
                PublicKeyCredential::class,
                'json'
            );

            if (! $publicKeyCredential->response instanceof AuthenticatorAssertionResponse) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ungültiger Response-Typ.',
                ]);
            }

            // Gespeicherten Credential-Record für Signatur-Prüfung laden
            $credentialSource = $this->passkeyRepository->findOneByCredentialId(
                $publicKeyCredential->rawId
            );

            if ($credentialSource === null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Passkey nicht gefunden.',
                ]);
            }

            // Assertion verifizieren
            $factory = new CeremonyStepManagerFactory();
            $factory->setAllowedOrigins([config('app.url')]);

            $validator = AuthenticatorAssertionResponseValidator::create(
                $factory->requestCeremony()
            );

            $host = parse_url(config('app.url'), PHP_URL_HOST);

            // $userHandle = null → Bibliothek prüft userHandle aus der Assertion-Response
            $updatedRecord = $validator->check(
                $credentialSource,
                $publicKeyCredential->response,
                $requestOptions,
                $host,
                null,
            );

            // Passkey-Datensatz für sign_count-Update + user_id
            $credentialIdEncoded = Base64UrlSafe::encodeUnpadded($publicKeyCredential->rawId);
            $passkey = Passkey::where('credential_id', $credentialIdEncoded)->first();

            if ($passkey === null || $passkey->user_type !== 'mand') {
                return response()->json([
                    'success' => false,
                    'message' => 'Passkey gehört keinem Mandanten.',
                ]);
            }

            $userId = (int) $passkey->user_id;

            $mand = MandUser::find($userId);
            if ($mand === null) {
                return response()->json([
                    'success' => false,
                    'message' => 'Benutzer nicht gefunden.',
                ]);
            }

            // Replay-Schutz: sign_count + last_used_at aktualisieren
            $passkey->update([
                'sign_count'   => $updatedRecord->counter,
                'last_used_at' => now(),
            ]);

            // Session aufbauen (gleiche Logik wie verifyTwoFactor)
            $sessionData = $this->sessionIntegrityService->buildSessionData('mand', $userId);

            $request->session()->regenerate();
            $request->session()->put('_user_type', $sessionData['user_type']);
            $request->session()->put('_mand_id',   $sessionData['mand_id']);
            $request->session()->put('_prompt_passkey', false);

            $this->passkeySessionStorage->clear($request);

            DB::connection('sessiondb')
                ->table('session')
                ->where('expires_at', '<', now())
                ->delete();

            return response()->json([
                'success'  => true,
                'redirect' => route('mandant.dashboard'),
            ]);

        } catch (Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('mandant.login')
            ->with('status', 'Sie wurden erfolgreich abgemeldet.');
    }
}
