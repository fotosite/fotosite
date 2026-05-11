<?php
/**
 * FILE:        app/Services/SessionDb/TwofaService.php
 * VERSION:     1.1.0
 *
 * FUNCTIONS:   generate()       — Creates a 6-digit code for a given purpose, stores hashed in DB, returns plain code
 *              verify()         — Checks code against hash; marks tfa_used = true on success (record kept for debugging)
 *              purgeExpired()   — Deletes rows where tfa_expires_at < now() OR tfa_used = true
 *
 * CALLS:       App\Models\SessionDb\TwofaCode::updateOrCreate()
 *              App\Models\SessionDb\TwofaCode::where()
 *
 * DB ACCESS:   sessiondb.twofa_code.tfa_id, user_type, user_id, tfa_purpose,
 *              tfa_code_hash, tfa_expires_at, tfa_used, created_at
 */

namespace App\Services\SessionDb;

use App\Models\SessionDb\TwofaCode;
use Carbon\Carbon;

class TwofaService extends SessionDbService
{
    private const CODE_LENGTH   = 6;
    private const VALID_MINUTES = 10;

    /**
     * Generates a new 6-digit code for the given user and purpose, stores it
     * (bcrypt-hashed), and returns the plain-text code for email delivery.
     */
    public function generate(string $userType, int $userId, string $tfaPurpose): string
    {
        $plain = str_pad((string) random_int(0, 999999), self::CODE_LENGTH, '0', STR_PAD_LEFT);

        TwofaCode::updateOrCreate(
            [
                'user_type'   => $userType,
                'user_id'     => $userId,
                'tfa_purpose' => $tfaPurpose,
            ],
            [
                'tfa_code_hash'  => password_hash($plain, PASSWORD_BCRYPT),
                'tfa_expires_at' => Carbon::now()->addMinutes(self::VALID_MINUTES),
                'tfa_used'       => false,
                'created_at'     => Carbon::now(),
            ]
        );

        return $plain;
    }

    /**
     * Verifies the submitted code. On success sets tfa_used = true and returns
     * true — the record is kept for debugging. Returns false if the record is
     * missing, already used, expired, or the code does not match.
     */
    public function verify(string $userType, int $userId, string $tfaPurpose, string $inputCode): bool
    {
        $record = TwofaCode::where('user_type', $userType)
            ->where('user_id', $userId)
            ->where('tfa_purpose', $tfaPurpose)
            ->where('tfa_used', false)
            ->first();

        if (! $record) {
            return false;
        }

        if (Carbon::now()->isAfter($record->tfa_expires_at)) {
            return false;
        }

        if (! password_verify($inputCode, $record->tfa_code_hash)) {
            return false;
        }

        $record->tfa_used = true;
        $record->save();

        return true;
    }

    /**
     * Removes rows that are either expired or already used.
     */
    public function purgeExpired(): int
    {
        return TwofaCode::where('tfa_expires_at', '<', Carbon::now())
            ->orWhere('tfa_used', true)
            ->delete();
    }
}
