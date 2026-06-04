<?php
/**
 * FILE:        app/Services/Passkey/PasskeySessionStorage.php
 * VERSION:     1.0.0
 *
 * FUNCTIONS:   store(Request $request, PublicKeyCredentialOptions $options): void
 *                  — Serialisiert Options (Creation oder Request) und speichert in Session
 *                    unter Key 'passkey_challenge'; schreibt session._passkey_challenge
 *              get(Request $request): ?PublicKeyCredentialOptions
 *                  — Liest + deserialisiert PublicKeyCredentialOptions aus Session
 *              clear(Request $request): void
 *                  — Löscht 'passkey_challenge' aus Session
 *
 * CALLS:       Webauthn\Denormalizer\WebauthnSerializerFactory::create()
 *              Webauthn\AttestationStatement\AttestationStatementSupportManager::create()
 *
 * DB ACCESS:   — (Session only; kein DB-Zugriff)
 */

namespace App\Services\Passkey;

use Illuminate\Http\Request;
use Symfony\Component\Serializer\SerializerInterface;
use Webauthn\AttestationStatement\AttestationStatementSupportManager;
use Webauthn\Denormalizer\WebauthnSerializerFactory;
use Webauthn\PublicKeyCredentialCreationOptions;
use Webauthn\PublicKeyCredentialOptions;
use Webauthn\PublicKeyCredentialRequestOptions;

class PasskeySessionStorage
{
    private const SESSION_KEY = 'passkey_challenge';

    private SerializerInterface $serializer;

    public function __construct()
    {
        $this->serializer = (new WebauthnSerializerFactory(
            AttestationStatementSupportManager::create()
        ))->create();
    }

    /**
     * Speichert serialisierte PublicKeyCredentialOptions in der Session.
     * Unterstützt sowohl CreationOptions als auch RequestOptions.
     */
    public function store(Request $request, PublicKeyCredentialOptions $options): void
    {
        $request->session()->put(self::SESSION_KEY, [
            'class' => $options::class,
            'data'  => $this->serializer->serialize($options, 'json'),
        ]);
    }

    /**
     * Liest und deserialisiert PublicKeyCredentialOptions aus der Session.
     * Gibt null zurück, wenn kein Challenge in der Session vorhanden ist.
     */
    public function get(Request $request): ?PublicKeyCredentialOptions
    {
        $stored = $request->session()->get(self::SESSION_KEY);

        if (!is_array($stored) || empty($stored['class']) || empty($stored['data'])) {
            return null;
        }

        $class = $stored['class'];

        // Nur bekannte Options-Klassen zulassen (Sicherheit gegen Session-Manipulation)
        if (!in_array($class, [PublicKeyCredentialCreationOptions::class, PublicKeyCredentialRequestOptions::class], true)) {
            return null;
        }

        return $this->serializer->deserialize($stored['data'], $class, 'json');
    }

    /**
     * Löscht den gespeicherten Challenge aus der Session.
     */
    public function clear(Request $request): void
    {
        $request->session()->forget(self::SESSION_KEY);
    }
}
