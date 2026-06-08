<?php
/**
 * FILE:        app/Http/Controllers/UserDb/CustLoginController.php
 * VERSION:     1.8.1
 * AUTOR:       Martin Wagner
 * DATUM:       2026-06-08
 *
 * ZWECK:       Cust-Login — registrierter Login mit optionaler 2FA,
 *              anonymer Login per Passwort-Sequenz, Passkey-Login, Logout.
 *
 * FUNCTIONS:   showLogin()       — Zeigt das Cust-Login-Formular an.
 *                                  Reads: —
 *              handleLogin()     — Validiert cust_email + password; sucht CustUser;
 *                                  ermittelt bevorzugten Mandanten via CustPcode;
 *                                  prüft 2FA-Pflicht via mand_cust_2fa; baut Session
 *                                  direkt (OS via detectOsPlatform() erkennen, ua_hash
 *                                  berechnen, _prompt_passkey/_passkey_os/
 *                                  _passkey_uahash anhand vorhandenem Passkey und
 *                                  passkey_dismissed-Eintrag setzen) oder leitet zu
 *                                  customer.login.2fa.
 *                                  Reads: userdb.cust_user.cust_id, cust_email,
 *                                         cust_pw_hash, cust_firstname
 *                                         userdb.cust_pcode.pcode_id, cust_id, mand_id,
 *                                         cust_passcode, pcode_prefstat
 *                                         userdb.mand_user.mand_id, mand_cust_2fa
 *                                         userdb.passkey_dismissed.user_type, user_id,
 *                                         os, ua_hash
 *              handleAnonLogin() — Validiert Passwort gegen alle aktiven pw_lists;
 *                                  ermittelt Mandant + Sicherheitsstufe; baut anonyme
 *                                  Session ohne cust_id.
 *                                  Reads: sessiondb.pw_list.mand_id, pw1–pw6,
 *                                         valid_from, valid_until
 *              showTwoFactor()   — Prüft pending_cust_id in Session;
 *                                  gibt customer.auth.two-factor zurück.
 *                                  Reads: —
 *              verifyTwoFactor() — Validiert tfa_code (digits:6); liest pending_*
 *                                  aus Session; delegiert an TwofaService::verifyCode();
 *                                  bei Erfolg: Session aufbauen, OS via
 *                                  detectOsPlatform() erkennen, ua_hash berechnen,
 *                                  Passkey-Prompt (_prompt_passkey, _passkey_os,
 *                                  _passkey_uahash) anhand vorhandenem Passkey und
 *                                  passkey_dismissed-Eintrag setzen, pending_*
 *                                  vergessen, Redirect zu customer.dashboard.
 *                                  Reads: sessiondb.twofa_code.* (via TwofaService)
 *                                         userdb.passkey_dismissed.user_type, user_id,
 *                                         os, ua_hash
 *              passkeyOptions()  — Erstellt userless PublicKeyCredentialRequestOptions
 *                                  (discoverable credential flow), speichert Challenge
 *                                  in Session, gibt JSON zurück.
 *                                  Reads: —
 *              passkeyLogin()    — Verifiziert Passkey-Assertion; sucht Passkey per
 *                                  credential_id; lädt bevorzugten Mandanten via
 *                                  CustPcode; aktualisiert sign_count + last_used_at;
 *                                  baut Session auf; setzt _prompt_passkey = false;
 *                                  gibt JSON mit redirect zurück.
 *                                  Reads:  userdb.passkey.credential_id, public_key,
 *                                          user_type, user_id, sign_count
 *                                          userdb.cust_pcode.mand_id, cust_passcode,
 *                                          pcode_prefstat, pcode_id
 *                                  Writes: userdb.passkey.sign_count, last_used_at
 *              logout()          — Invalidiert Session, bereinigt abgelaufene Sessions,
 *                                  Redirect zu home mit status-Flash.
 *                                  Writes: sessiondb.session (DELETE expires_at < now)
 *
 * CALLS:       App\Models\UserDb\CustUser::where()->first()
 *              detectOsPlatform() (app/helpers.php)
 *              App\Models\UserDb\CustPcode::where()->orderByDesc()->first()
 *              App\Models\UserDb\MandUser::find()
 *              App\Models\UserDb\Passkey::where()->first()
 *              App\Models\UserDb\PasskeyDismissed::where()->exists()
 *              App\Models\SessionDb\PwList::where()->get()
 *              App\Mail\TwoFactorCodeMail
 *              App\Services\SessionDb\TwofaService::generateCode()
 *              App\Services\SessionDb\TwofaService::verifyCode()
 *              App\Services\SessionDb\SessionIntegrityService::buildSessionData()
 *              App\Services\Passkey\PasskeyRepository::findOneByCredentialId()
 *              App\Services\Passkey\PasskeySessionStorage::store()
 *              App\Services\Passkey\PasskeySessionStorage::get()
 *              App\Services\Passkey\PasskeySessionStorage::clear()
 *              Webauthn\AuthenticatorAssertionResponseValidator::check()
 *              Webauthn\CeremonyStep\CeremonyStepManagerFactory::requestCeremony()
 *              Illuminate\Support\Facades\Hash::check()
 *              Illuminate\Support\Facades\Mail::to()->send()
 *              App\Models\SessionDb\Session::where()->delete()
 *
 * DB ACCESS:   userdb.cust_user.cust_id, cust_email, cust_pw_hash, cust_firstname
 *              userdb.cust_pcode.pcode_id, cust_id, mand_id, cust_passcode, pcode_prefstat
 *              userdb.mand_user.mand_id, mand_cust_2fa
 *              userdb.passkey.credential_id, public_key, user_type, user_id,
 *              sign_count, last_used_at
 *              userdb.passkey_dismissed.user_type, user_id, os, ua_hash
 *              sessiondb.pw_list.mand_id, pw1, pw2, pw3, pw4, pw5, pw6,
 *              valid_from, valid_until
 *              sessiondb.twofa_code.* (via TwofaService)
 *              sessiondb.session.expires_at (DELETE abgelaufene Sessions bei Login & Logout)
 *              sessiondb.session.sess_token (DELETE eigene Session bei Logout)
 */

namespace App\Http\Controllers\UserDb;

use function detectOsPlatform;

use App\Mail\TwoFactorCodeMail;
use App\Models\SessionDb\PwList;
use App\Models\UserDb\CustPcode;
use App\Models\UserDb\CustUser;
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
use Illuminate\Http\Response;
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

class CustLoginController extends UserDbController
{
    private SerializerInterface $serializer;

    public function __construct(
        private readonly TwofaService           $twofaService,
        private readonly SessionIntegrityService $sessionIntegrityService,
        private readonly PasskeyRepository       $passkeyRepository,
        private readonly PasskeySessionStorage   $passkeySessionStorage,
    ) {
        $this->serializer = (new WebauthnSerializerFactory(
            AttestationStatementSupportManager::create()
        ))->create();
    }

    public function showLogin(): Response
    {
        return response('cust login ok');
    }

    public function handleLogin(Request $request): RedirectResponse
    {
        $request->validate([
            'cust_email' => ['required', 'email'],
            'password'   => ['required', 'string'],
        ]);

        $cust = CustUser::where('cust_email', $request->cust_email)->first();

        if (! $cust || ! Hash::check($request->password, $cust->cust_pw_hash)) {
            return back()
                ->withErrors(['credentials' => 'Diese Zugangsdaten sind uns nicht bekannt.'])
                ->with('login_page', 'cust')
                ->with('cust_tab', 'reg');
        }

        $pcode = CustPcode::where('cust_id', $cust->cust_id)
            ->orderByDesc('pcode_prefstat')
            ->orderByDesc('pcode_id')
            ->first();

        if (! $pcode) {
            return back()
                ->withErrors(['credentials' => 'Kein Mandant zugeordnet.'])
                ->with('login_page', 'cust')
                ->with('cust_tab', 'reg');
        }

        $mand = MandUser::find($pcode->mand_id);

        if (! $mand) {
            return back()
                ->withErrors(['credentials' => 'Kein Mandant zugeordnet.'])
                ->with('login_page', 'cust')
                ->with('cust_tab', 'reg');
        }

        $secLevel  = (int) $pcode->cust_passcode;
        $threshold = $mand->mand_cust_2fa;

        if ($secLevel >= $threshold && $threshold < 7) {
            $code = $this->twofaService->generateCode('cust', $cust->cust_id, 0);

            Mail::to($cust->cust_email)
                ->send(new TwoFactorCodeMail($code, $cust->cust_firstname ?? ''));

            $request->session()->put('pending_cust_id',   $cust->cust_id);
            $request->session()->put('pending_mand_id',   $pcode->mand_id);
            $request->session()->put('pending_sec_level', $pcode->cust_passcode);

            return redirect()->route('customer.login.2fa');
        }

        $sessionData = $this->sessionIntegrityService->buildSessionData('cust', $cust->cust_id);

        $request->session()->regenerate();
        $request->session()->put('_user_type',     $sessionData['user_type']);
        $request->session()->put('_cust_id',       $cust->cust_id);
        $request->session()->put('_mand_id',       $pcode->mand_id);
        $request->session()->put('_sec_level',     $pcode->cust_passcode);
        $request->session()->put('_last_activity', time());

        // OS erkennen
        $os = detectOsPlatform($request->userAgent());

        // Passkey für dieses OS und diesen User bereits vorhanden?
        $hasPasskey = Passkey::where('user_type', 'cust')
            ->where('user_id', $cust->cust_id)
            ->exists();

        // ua_hash berechnen (analog SessionHijackProtection)
        $uaHash = hash('sha256', $request->userAgent() ?? '');

        // "Nie wieder fragen" für dieses Gerät + OS gesetzt?
        $neverAsk = \App\Models\UserDb\PasskeyDismissed::where('user_type', 'cust')
            ->where('user_id', $cust->cust_id)
            ->where('os', $os)
            ->where('ua_hash', $uaHash)
            ->exists();

        // Prompt setzen
        session([
            '_prompt_passkey' => !$hasPasskey && !$neverAsk && $os !== 'unknown',
            '_passkey_os'     => $os,
            '_passkey_uahash' => $uaHash,
        ]);

        // Abgelaufene Sessions bereinigen
        \App\Models\SessionDb\Session::where('expires_at', '<', now())
            ->delete();

        return redirect()->route('customer.dashboard');
    }

    public function handleAnonLogin(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'string', 'min:8'],
        ]);

        $password = $request->input('password');

        $pwLists = PwList::where('valid_from', '<=', now())
            ->where('valid_until', '>=', now())
            ->get();

        $mandId   = null;
        $secLevel = null;

        foreach ($pwLists as $pwList) {
            foreach (['pw1', 'pw2', 'pw3', 'pw4', 'pw5', 'pw6'] as $index => $field) {
                try {
                    if (decrypt($pwList->$field) === $password) {
                        $mandId   = $pwList->mand_id;
                        $secLevel = $index + 1;
                        break 2;
                    }
                } catch (\Exception $e) {
                    continue;
                }
            }
        }

        if ($mandId === null) {
            return back()
                ->withErrors(['password' => 'Passwort nicht gültig oder abgelaufen.'])
                ->with('login_page', 'cust')
                ->with('cust_tab', 'anon');
        }

        $request->session()->regenerate();
        $request->session()->put('_user_type',     'anon');
        $request->session()->put('_mand_id',       $mandId);
        $request->session()->put('_sec_level',     $secLevel);
        $request->session()->put('_last_activity', time());

        return redirect()->route('customer.dashboard');
    }

    public function showTwoFactor(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('pending_cust_id')) {
            return redirect()->route('customer.login');
        }

        return view('customer.auth.two-factor');
    }

    public function verifyTwoFactor(Request $request): RedirectResponse
    {
        $request->validate([
            'tfa_code' => ['required', 'digits:6'],
        ]);

        $custId   = $request->session()->get('pending_cust_id');
        $mandId   = $request->session()->get('pending_mand_id');
        $secLevel = $request->session()->get('pending_sec_level');

        if (! $custId || ! $mandId || $secLevel === null) {
            return redirect()->route('customer.login');
        }

        $verified = $this->twofaService->verifyCode(
            $request->tfa_code,
            'cust',
            (int) $custId
        );

        if (! $verified) {
            return back()->withErrors(['tfa_code' => 'Ungültiger oder abgelaufener Code.']);
        }

        $request->session()->regenerate();
        $request->session()->put('_user_type',     'cust');
        $request->session()->put('_cust_id',       $custId);
        $request->session()->put('_mand_id',       $mandId);
        $request->session()->put('_sec_level',     $secLevel);
        $request->session()->put('_last_activity', time());

        // OS erkennen
        $os = detectOsPlatform($request->userAgent());

        // Passkey für dieses OS und diesen User bereits vorhanden?
        $hasPasskey = Passkey::where('user_type', 'cust')
            ->where('user_id', $custId)
            ->exists();

        // ua_hash berechnen (analog SessionHijackProtection)
        $uaHash = hash('sha256', $request->userAgent() ?? '');

        // "Nie wieder fragen" für dieses Gerät + OS gesetzt?
        $neverAsk = \App\Models\UserDb\PasskeyDismissed::where('user_type', 'cust')
            ->where('user_id', $custId)
            ->where('os', $os)
            ->where('ua_hash', $uaHash)
            ->exists();

        // Prompt setzen
        session([
            '_prompt_passkey' => !$hasPasskey && !$neverAsk && $os !== 'unknown',
            '_passkey_os'     => $os,
            '_passkey_uahash' => $uaHash,
        ]);

        $request->session()->forget(['pending_cust_id', 'pending_mand_id', 'pending_sec_level']);

        // Abgelaufene Sessions bereinigen
        \App\Models\SessionDb\Session::where('expires_at', '<', now())
            ->delete();

        return redirect()->route('customer.dashboard');
    }

    /**
     * Liefert PublicKeyCredentialRequestOptions für den userless/discoverable flow.
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
     * Verifiziert eine Passkey-Assertion und baut die Cust-Session auf.
     * Lädt bevorzugten Mandanten + Sicherheitsstufe aus CustPcode.
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

            $updatedRecord = $validator->check(
                $credentialSource,
                $publicKeyCredential->response,
                $requestOptions,
                $host,
                null,
            );

            // Passkey-Datensatz für user_id + sign_count-Update
            $credentialIdEncoded = Base64UrlSafe::encodeUnpadded($publicKeyCredential->rawId);
            $passkey = Passkey::where('credential_id', $credentialIdEncoded)->first();

            if ($passkey === null || $passkey->user_type !== 'cust') {
                return response()->json([
                    'success' => false,
                    'message' => 'Passkey gehört keinem Kunden.',
                ]);
            }

            $userId = (int) $passkey->user_id;

            // Replay-Schutz: sign_count + last_used_at aktualisieren
            $passkey->update([
                'sign_count'   => $updatedRecord->counter,
                'last_used_at' => now(),
            ]);

            // Bevorzugten Mandanten + Sicherheitsstufe aus CustPcode laden
            $pcode = CustPcode::where('cust_id', $userId)
                ->orderByDesc('pcode_prefstat')
                ->orderByDesc('pcode_id')
                ->first();

            // Session aufbauen (gleiche Struktur wie handleLogin)
            $sessionData = $this->sessionIntegrityService->buildSessionData('cust', $userId);

            $request->session()->regenerate();
            $request->session()->put('_user_type',     $sessionData['user_type']);
            $request->session()->put('_cust_id',       $userId);
            $request->session()->put('_mand_id',       $pcode?->mand_id);
            $request->session()->put('_sec_level',     $pcode?->cust_passcode);
            $request->session()->put('_last_activity', time());
            $request->session()->put('_prompt_passkey', false);

            $this->passkeySessionStorage->clear($request);

            DB::connection('sessiondb')
                ->table('session')
                ->where('expires_at', '<', now())
                ->delete();

            return response()->json([
                'success'  => true,
                'redirect' => route('customer.dashboard'),
            ]);

        } catch (Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function logout(Request $request): RedirectResponse
    {
        // Eigene Session aus DB löschen
        $sessToken = session()->getId();
        \App\Models\SessionDb\Session::where('sess_token', $sessToken)
            ->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        DB::connection('sessiondb')
            ->table('session')
            ->where('expires_at', '<', now())
            ->delete();

        return redirect()->route('home')
            ->with('status', 'Sie wurden erfolgreich abgemeldet.');
    }
}
