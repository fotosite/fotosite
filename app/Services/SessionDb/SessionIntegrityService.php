<?php
/**
 * FILE:        app/Services/SessionDb/SessionIntegrityService.php
 * VERSION:     1.0
 * AUTOR:       Martin Wagner
 * DATUM:       2026-05-26
 *
 * ZWECK:       Check 1 — Baut und validiert die ID-Felder eines Session-Datensatzes.
 *              Stellt sicher, dass genau ein *_id-Feld je User-Typ gesetzt ist
 *              (anon: alle null). Grundlage für spätere SessionCreateService-Logik.
 *
 * FUNCTIONS:   buildSessionData(string $userType, int $userId): array
 *                  — Gibt Array mit user_type und den drei *_id-Feldern zurück.
 *                  Setzt genau einen *_id-Wert je nach $userType; anon: alle null.
 *                  Wirft InvalidArgumentException bei unbekanntem $userType.
 *
 *              validateSessionData(array $data): bool
 *                  — Prüft: user_type ist einer von anon/cust/mand/syst,
 *                  genau ein *_id-Feld gesetzt und passend zum user_type
 *                  (anon: alle *_id null erlaubt).
 *
 * CALLS:       —
 *
 * DB ACCESS:   sessiondb.session.user_type, syst_id, mand_id, cust_id
 */

namespace App\Services\SessionDb;

use InvalidArgumentException;

class SessionIntegrityService extends SessionDbService
{
    private const VALID_USER_TYPES = ['anon', 'cust', 'mand', 'syst'];

    private const ID_FIELD_MAP = [
        'syst' => 'syst_id',
        'mand' => 'mand_id',
        'cust' => 'cust_id',
    ];

    public function buildSessionData(string $userType, int $userId): array
    {
        if (! in_array($userType, self::VALID_USER_TYPES, true)) {
            throw new InvalidArgumentException("Unbekannter user_type: {$userType}");
        }

        $data = [
            'user_type' => $userType,
            'syst_id'   => null,
            'mand_id'   => null,
            'cust_id'   => null,
        ];

        if (isset(self::ID_FIELD_MAP[$userType])) {
            $data[self::ID_FIELD_MAP[$userType]] = $userId;
        }

        return $data;
    }

    public function validateSessionData(array $data): bool
    {
        if (! isset($data['user_type']) || ! in_array($data['user_type'], self::VALID_USER_TYPES, true)) {
            return false;
        }

        $userType = $data['user_type'];
        $setCount = 0;

        foreach (['syst_id', 'mand_id', 'cust_id'] as $field) {
            if (($data[$field] ?? null) !== null) {
                $setCount++;
            }
        }

        if ($userType === 'anon') {
            return $setCount === 0;
        }

        if ($setCount !== 1) {
            return false;
        }

        $expected = self::ID_FIELD_MAP[$userType];

        return ($data[$expected] ?? null) !== null;
    }
}
