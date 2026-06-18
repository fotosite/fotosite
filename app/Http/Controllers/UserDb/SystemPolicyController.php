<?php
/**
 * FILE:        app/Http/Controllers/UserDb/SystemPolicyController.php
 * VERSION:     1.0.0
 *
 * ZWECK:       Syst-Verwaltung der globalen Policy-Versionen (Datenschutz,
 *              Upload-Bedingungen). Erhöhen einer Version löst beim nächsten
 *              Login aller betroffenen mand/cust das Policy-Update-Popup aus
 *              (siehe App\Http\Middleware\CheckPolicyVersion).
 *
 * FUNCTIONS:   index()          — Liest beide aktuellen Versionen aus der DB;
 *                                  gibt system.policy_versionen zurück.
 *                                  Reads: userdb.policy_versions.pv_key, pv_value
 *              incrementDs()     — Erhöht ds_version (Minor-Schritt: 1.0→1.1,
 *                                  1.9→2.0); speichert; Redirect mit Erfolgsmeldung.
 *                                  Reads/Writes: userdb.policy_versions.pv_value
 *                                  (pv_key='ds_version')
 *              incrementUpload() — Analog für upload_version.
 *                                  Reads/Writes: userdb.policy_versions.pv_value
 *                                  (pv_key='upload_version')
 *              incrementMinorVersion(string)  — Private Helper: parst "X.Y",
 *                                  erhöht Y; bei Y=10 → X+1, Y=0.
 *
 * CALLS:       App\Models\UserDb\PolicyVersion::find()
 *              App\Models\UserDb\PolicyVersion::updateOrCreate()
 *
 * DB ACCESS:   userdb.policy_versions.pv_key, pv_value
 */

namespace App\Http\Controllers\UserDb;

use App\Models\UserDb\PolicyVersion;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;

class SystemPolicyController extends UserDbController
{
    public function index(): View
    {
        $dsVersion     = PolicyVersion::find('ds_version')?->pv_value     ?? '—';
        $uploadVersion = PolicyVersion::find('upload_version')?->pv_value ?? '—';

        return view('system.policy_versionen', compact('dsVersion', 'uploadVersion'));
    }

    public function incrementDs(): RedirectResponse
    {
        $current = PolicyVersion::find('ds_version')?->pv_value ?? '1.0';
        $new     = $this->incrementMinorVersion($current);

        PolicyVersion::updateOrCreate(['pv_key' => 'ds_version'], ['pv_value' => $new]);

        return redirect()->route('system.policy.index')
            ->with('status', "DS-Version auf {$new} gesetzt. Alle User sehen beim nächsten Login das Popup.");
    }

    public function incrementUpload(): RedirectResponse
    {
        $current = PolicyVersion::find('upload_version')?->pv_value ?? '1.0';
        $new     = $this->incrementMinorVersion($current);

        PolicyVersion::updateOrCreate(['pv_key' => 'upload_version'], ['pv_value' => $new]);

        return redirect()->route('system.policy.index')
            ->with('status', "Upload-Version auf {$new} gesetzt. Alle User sehen beim nächsten Login das Popup.");
    }

    private function incrementMinorVersion(string $version): string
    {
        [$major, $minor] = array_pad(explode('.', $version, 2), 2, '0');
        $major = (int) $major;
        $minor = (int) $minor + 1;

        if ($minor >= 10) {
            $major++;
            $minor = 0;
        }

        return "{$major}.{$minor}";
    }
}
