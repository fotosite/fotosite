<?php
/**
 * FILE:        app/Services/Passkey/PasskeyRepository.php
 * VERSION:     1.1.0
 *
 * FUNCTIONS:   findOneByCredentialId(string $publicKeyCredentialId)
 *                  — Sucht Credential via credential_id (base64url); liest userdb.passkey.public_key
 *              findAllForUserEntity(PublicKeyCredentialUserEntity $userEntity): array
 *                  — Gibt alle Passkeys eines Users zurück; liest userdb.passkey per user_type+user_id
 *              saveCredentialSource(CredentialRecord $credentialSource): void
 *                  — Insert oder Update; schreibt userdb.passkey.*
 *
 * CALLS:       App\Models\UserDb\Passkey (Model)
 *              Webauthn\Denormalizer\WebauthnSerializerFactory::create()
 *              Webauthn\AttestationStatement\AttestationStatementSupportManager::create()
 *
 * DB ACCESS:   userdb.passkey.credential_id, user_handle, user_type, user_id, public_key
 *
 * NOTE:        PublicKeyCredentialSourceRepository wurde in web-auth/webauthn-lib 5.x entfernt.
 *              Diese Klasse implementiert kein Interface der Bibliothek; die Methoden-
 *              signaturen entsprechen dem früheren v4-Interface und werden von den
 *              eigenen Passkey-Controllern direkt aufgerufen.
 */

namespace App\Services\Passkey;

use App\Models\UserDb\Passkey;
use ParagonIE\ConstantTime\Base64UrlSafe;
use Symfony\Component\Serializer\SerializerInterface;
use Webauthn\AttestationStatement\AttestationStatementSupportManager;
use Webauthn\Denormalizer\WebauthnSerializerFactory;
use Webauthn\CredentialRecord;
use Webauthn\PublicKeyCredentialUserEntity;

class PasskeyRepository
{
    private SerializerInterface $serializer;

    public function __construct(private Passkey $model)
    {
        $this->serializer = (new WebauthnSerializerFactory(
            AttestationStatementSupportManager::create()
        ))->create();
    }

    public function findOneByCredentialId(string $publicKeyCredentialId): ?CredentialRecord
    {
        $credentialIdEncoded = Base64UrlSafe::encodeUnpadded($publicKeyCredentialId);
        $record = $this->model->where('credential_id', $credentialIdEncoded)->first();

        if ($record === null) {
            return null;
        }

        return $this->serializer->deserialize(
            $record->public_key,
            CredentialRecord::class,
            'json'
        );
    }

    /**
     * @return CredentialRecord[]
     */
    public function findAllForUserEntity(PublicKeyCredentialUserEntity $userEntity): array
    {
        // userHandle bytes = base64url("user_type:user_id") — decode to extract parts
        $decoded = Base64UrlSafe::decode($userEntity->id);
        [$userType, $userId] = explode(':', $decoded, 2);

        return $this->model
            ->where('user_type', $userType)
            ->where('user_id', (int) $userId)
            ->get()
            ->map(fn ($r) => $this->serializer->deserialize(
                $r->public_key,
                CredentialRecord::class,
                'json'
            ))
            ->all();
    }

    public function saveCredentialSource(CredentialRecord $credentialSource): void
    {
        $credentialIdEncoded = Base64UrlSafe::encodeUnpadded($credentialSource->publicKeyCredentialId);
        $userHandleEncoded   = Base64UrlSafe::encodeUnpadded($credentialSource->userHandle);

        // userHandle bytes = base64url("user_type:user_id") — decode to extract parts
        $decoded = Base64UrlSafe::decode($credentialSource->userHandle);
        [$userType, $userId] = explode(':', $decoded, 2);

        $json = $this->serializer->serialize($credentialSource, 'json');

        $this->model->updateOrCreate(
            ['credential_id' => $credentialIdEncoded],
            [
                'user_handle' => $userHandleEncoded,
                'user_type'   => $userType,
                'user_id'     => (int) $userId,
                'public_key'  => $json,
            ]
        );
    }
}
