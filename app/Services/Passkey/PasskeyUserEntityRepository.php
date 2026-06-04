<?php
/**
 * FILE:        app/Services/Passkey/PasskeyUserEntityRepository.php
 * VERSION:     1.0.0
 *
 * FUNCTIONS:   findOneByUserHandle(string $userHandle): ?PublicKeyCredentialUserEntity
 *                  — Dekodiert userHandle (base64url → user_type:user_id),
 *                    sucht in mand_user oder cust_user;
 *                    liest userdb.mand_user.mand_id, mand_uname
 *                        userdb.cust_user.cust_id, cust_uname, cust_firstname, cust_lastname
 *
 * CALLS:       App\Models\UserDb\MandUser::find()
 *              App\Models\UserDb\CustUser::find()
 *
 * DB ACCESS:   userdb.mand_user.mand_id, mand_uname
 *              userdb.cust_user.cust_id, cust_uname, cust_firstname, cust_lastname
 */

namespace App\Services\Passkey;

use App\Models\UserDb\CustUser;
use App\Models\UserDb\MandUser;
use ParagonIE\ConstantTime\Base64UrlSafe;
use Webauthn\PublicKeyCredentialUserEntity;

class PasskeyUserEntityRepository
{
    /**
     * Findet den User anhand des userHandles.
     * Konvention: userHandle (raw bytes) = base64url("user_type:user_id")
     * Beispiel:   base64url("cust:42") oder base64url("mand:7")
     */
    public function findOneByUserHandle(string $userHandle): ?PublicKeyCredentialUserEntity
    {
        $decoded = Base64UrlSafe::decode($userHandle);
        [$userType, $userId] = explode(':', $decoded, 2);
        $userId = (int) $userId;

        if ($userType === 'mand') {
            $user = MandUser::find($userId);
            if ($user === null) {
                return null;
            }
            return PublicKeyCredentialUserEntity::create(
                $user->mand_uname,
                $userHandle,
                $user->mand_uname
            );
        }

        if ($userType === 'cust') {
            $user = CustUser::find($userId);
            if ($user === null) {
                return null;
            }
            $displayName = trim($user->cust_firstname . ' ' . $user->cust_lastname);
            return PublicKeyCredentialUserEntity::create(
                $user->cust_uname,
                $userHandle,
                $displayName ?: $user->cust_uname
            );
        }

        return null;
    }
}
