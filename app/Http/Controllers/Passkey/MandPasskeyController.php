<?php
/**
 * FILE:        app/Http/Controllers/Passkey/MandPasskeyController.php
 * VERSION:     1.1.0
 * AUTOR:       Martin Wagner
 * DATUM:       2026-06-04
 *
 * ZWECK:       Passkey-Registrierung für Mandanten — Liste, Options, Register,
 *              Umbenennen, Löschen.
 *
 * FUNCTIONS:   index()               — Passkey-Liste für eingeloggten Mandanten
 *                                      Reads: userdb.passkey.pk_id, device_name,
 *                                             created_at, last_used_at
 *              registrationOptions() — Erstellt PublicKeyCredentialCreationOptions,
 *                                      speichert Challenge in Session, gibt JSON zurück
 *                                      Reads: userdb.mand_user.mand_email,
 *                                             mand_firstname, mand_lastname
 *              register()            — Verifiziert Attestation, speichert neuen Passkey
 *                                      Writes: userdb.passkey.*
 *              rename()              — Aktualisiert device_name eines Passkeys
 *                                      Writes: userdb.passkey.device_name
 *              destroy()             — Löscht einen Passkey
 *                                      Writes: userdb.passkey (DELETE)
 *
 * CALLS:       App\Models\UserDb\MandUser::find()
 *              App\Models\UserDb\Passkey::where()->get()
 *              App\Models\UserDb\Passkey::create()
 *              App\Models\UserDb\Passkey::where()->firstOrFail()
 *              App\Services\Passkey\PasskeySessionStorage::store()
 *              App\Services\Passkey\PasskeySessionStorage::get()
 *              App\Services\Passkey\PasskeySessionStorage::clear()
 *              Webauthn\AuthenticatorAttestationResponseValidator::check()
 *              Webauthn\CeremonyStep\CeremonyStepManagerFactory::creationCeremony()
 *              Webauthn\Denormalizer\WebauthnSerializerFactory::create()
 *
 * DB ACCESS:   userdb.passkey.pk_id, user_type, user_id, credential_id,
 *              public_key, sign_count, device_name, created_at, last_used_at
 *              userdb.mand_user.mand_email, mand_firstname, mand_lastname
 */

namespace App\Http\Controllers\Passkey;

use App\Http\Controllers\Controller;
use App\Models\UserDb\MandUser;
use App\Models\UserDb\Passkey;
use App\Services\Passkey\PasskeyRepository;
use App\Services\Passkey\PasskeySessionStorage;
use Cose\Algorithms;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use ParagonIE\ConstantTime\Base64UrlSafe;
use Symfony\Component\Serializer\SerializerInterface;
use Throwable;
use Webauthn\AttestationStatement\AttestationStatementSupportManager;
use Webauthn\AuthenticatorAttestationResponse;
use Webauthn\AuthenticatorAttestationResponseValidator;
use Webauthn\AuthenticatorSelectionCriteria;
use Webauthn\CeremonyStep\CeremonyStepManagerFactory;
use Webauthn\Denormalizer\WebauthnSerializerFactory;
use Webauthn\PublicKeyCredential;
use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialParameters;
use Webauthn\PublicKeyCredentialRpEntity;
use Webauthn\PublicKeyCredentialSource;
use Webauthn\PublicKeyCredentialUserEntity;

class MandPasskeyController extends Controller
{
    private SerializerInterface $serializer;

    public function __construct(
        private readonly PasskeyRepository $repository,
        private readonly PasskeySessionStorage $sessionStorage,
    ) {
        $this->serializer = (new WebauthnSerializerFactory(
            AttestationStatementSupportManager::create()
        ))->create();
    }

    public function index(Request $request): View
    {
        $mandId = $request->session()->get('_mand_id');

        $passkeys = Passkey::where('user_type', 'mand')
            ->where('user_id', $mandId)
            ->orderByDesc('created_at')
            ->get();

        return view('mandant.passkey.index', ['passkeys' => $passkeys]);
    }

    public function registrationOptions(Request $request): JsonResponse
    {
        $mandId = $request->session()->get('_mand_id');
        $mand   = MandUser::find($mandId);

        if ($mand === null) {
            return response()->json(['error' => 'Nicht authentifiziert.'], 401);
        }

        $userHandle = Base64UrlSafe::encodeUnpadded("mand:{$mandId}");

        $userEntity = PublicKeyCredentialUserEntity::create(
            $mand->mand_email,
            $userHandle,
            trim(($mand->mand_firstname ?? '') . ' ' . ($mand->mand_lastname ?? '')),
        );

        $rpEntity = PublicKeyCredentialRpEntity::create(
            config('app.name'),
            parse_url(config('app.url'), PHP_URL_HOST),
        );

        $options = PublicKeyCredentialCreationOptions::create(
            rp:   $rpEntity,
            user: $userEntity,
            challenge: random_bytes(32),
            pubKeyCredParams: [
                PublicKeyCredentialParameters::create('public-key', Algorithms::COSE_ALGORITHM_ES256),
                PublicKeyCredentialParameters::create('public-key', Algorithms::COSE_ALGORITHM_RS256),
            ],
            authenticatorSelection: AuthenticatorSelectionCriteria::create(
                authenticatorAttachment: AuthenticatorSelectionCriteria::AUTHENTICATOR_ATTACHMENT_PLATFORM,
                userVerification:        AuthenticatorSelectionCriteria::USER_VERIFICATION_REQUIREMENT_REQUIRED,
                residentKey:             AuthenticatorSelectionCriteria::RESIDENT_KEY_REQUIREMENT_REQUIRED,
            ),
            timeout: 60000,
        );

        $this->sessionStorage->store($request, $options);

        return response()->json(
            json_decode($this->serializer->serialize($options, 'json'), true)
        );
    }

    public function register(Request $request): JsonResponse
    {
        $mandId = $request->session()->get('_mand_id');

        $creationOptions = $this->sessionStorage->get($request);
        if (! $creationOptions instanceof PublicKeyCredentialCreationOptions) {
            return response()->json([
                'success' => false,
                'message' => 'Keine Challenge gefunden. Bitte die Seite neu laden.',
            ]);
        }

        try {
            $credentialJson = json_encode($request->input('credential', []));

            /** @var PublicKeyCredential $publicKeyCredential */
            $publicKeyCredential = $this->serializer->deserialize(
                $credentialJson,
                PublicKeyCredential::class,
                'json'
            );

            if (! $publicKeyCredential->response instanceof AuthenticatorAttestationResponse) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ungültiger Response-Typ.',
                ]);
            }

            $factory = new CeremonyStepManagerFactory();
            $factory->setAllowedOrigins([config('app.url')]);

            $validator = AuthenticatorAttestationResponseValidator::create(
                $factory->creationCeremony()
            );

            $host = parse_url(config('app.url'), PHP_URL_HOST);

            $credentialRecord = $validator->check(
                $publicKeyCredential->response,
                $creationOptions,
                $host,
            );

            $credentialSource = PublicKeyCredentialSource::fromCredentialRecord($credentialRecord);

            Passkey::create([
                'user_type'     => 'mand',
                'user_id'       => $mandId,
                'credential_id' => Base64UrlSafe::encodeUnpadded($credentialRecord->publicKeyCredentialId),
                'public_key'    => $this->serializer->serialize($credentialSource, 'json'),
                'sign_count'    => $credentialRecord->counter,
                'device_name'   => $request->input('device_name', 'Unbekanntes Gerät'),
                'created_at'    => now(),
            ]);

            $this->sessionStorage->clear($request);

            return response()->json(['success' => true]);

        } catch (Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function rename(Request $request, int $id): JsonResponse
    {
        $mandId = $request->session()->get('_mand_id');

        $request->validate(['device_name' => ['required', 'string', 'max:100']]);

        $passkey = Passkey::where('pk_id', $id)
            ->where('user_type', 'mand')
            ->where('user_id', $mandId)
            ->firstOrFail();

        $passkey->update(['device_name' => $request->input('device_name')]);

        return response()->json(['success' => true]);
    }

    public function destroy(Request $request, int $id): RedirectResponse
    {
        $mandId = $request->session()->get('_mand_id');

        $passkey = Passkey::where('pk_id', $id)
            ->where('user_type', 'mand')
            ->where('user_id', $mandId)
            ->firstOrFail();

        $passkey->delete();

        return redirect()->route('mandant.passkeys')
            ->with('status', 'Passkey wurde erfolgreich gelöscht.');
    }
}
