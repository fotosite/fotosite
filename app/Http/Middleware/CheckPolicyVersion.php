<?php
/**
 * FILE:        app/Http/Middleware/CheckPolicyVersion.php
 * VERSION:     1.3.0
 *
 * ZWECK:       Prüft nach dem Login, ob mand/cust die aktuelle Datenschutz- und
 *              Upload-Policy-Version kennen. Falls nicht: Redirect auf die
 *              blockierende Popup-Seite (policy.update).
 *              mand: prüft ds_version UND upload_terms_version (beide in
 *              mand_user gespeichert).
 *              cust: prüft ds_version UND upload_terms_version (beide in
 *              cust_user gespeichert, seit DDL-Ergänzung von cust_user —
 *              vorher nur Session-Flag, dadurch erschien das Popup bei jedem
 *              Login erneut).
 *              Sind beide Policies veraltet, wird zuerst DS angezeigt; erst
 *              nach deren Bestätigung (nächster Request) erscheint die
 *              Upload-Meldung — ergibt sich automatisch aus den sequenziellen
 *              Prüfungen mit Early-Return.
 *
 * FUNCTIONS:   handle(Request, Closure): Response
 *                  — Bricht sofort ab auf den policy.update/.confirm-Routen
 *                  selbst (verhindert Redirect-Schleife) und wenn kein
 *                  _user_type in der Session steht (anon/nicht eingeloggt).
 *                  Für mand/cust: lädt den User, vergleicht Versionen,
 *                  redirected bei Abweichung mit session(['_policy_update' => ...]).
 *
 * CALLS:       App\Models\UserDb\PolicyVersion::get()
 *              App\Models\UserDb\MandUser::find()
 *              App\Models\UserDb\CustUser::find()
 *
 * DB ACCESS:   userdb.policy_versions.pv_key, pv_value
 *              userdb.mand_user.mand_id, ds_version, upload_terms_version
 *              userdb.cust_user.cust_id, ds_version, upload_terms_version
 *
 * CHANGES:     1.1.0 (2026-06-18) cust-Upload-Zweig: Vergleich gegen
 *              cust_user.upload_terms_version statt Session-Flag
 *              (_cust_upload_hinweis_version) — analog zum mand-Zweig.
 *              1.2.0 (2026-06-21) Ausschluss um '*.welcome*' erweitert —
 *              verhindert Redirect-Ping-Pong mit CheckWelcome, wenn ein
 *              Account gleichzeitig show_welcome=1 UND eine veraltete
 *              ds_version/upload_terms_version hat (typisch: frisch
 *              eingeladener mand nach Passwort-Reset).
 *              1.3.0 (2026-06-21) Ausschluss um '*.datenschutz.*' erweitert —
 *              die "ansehen"-Links auf der Policy-Update-Seite öffnen die
 *              DS-Erläuterung/Upload-Bedingungen-PDF (routes/customer.php,
 *              customer.datenschutz.*) im selben Browser/derselben Session;
 *              ohne Ausschluss bounced dieser Check den Nutzer zurück auf
 *              policy.update, bevor er die Erläuterung lesen kann.
 */

namespace App\Http\Middleware;

use App\Models\UserDb\CustUser;
use App\Models\UserDb\MandUser;
use App\Models\UserDb\PolicyVersion;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPolicyVersion
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->routeIs('*.policy.*') || $request->routeIs('*.welcome*') || $request->routeIs('*.datenschutz.*')) {
            return $next($request);
        }

        if (! $request->hasSession() || ! $request->session()->isStarted()) {
            return $next($request);
        }

        $userType = $request->session()->get('_user_type');

        if ($userType === 'mand') {
            $mand = MandUser::find($request->session()->get('_mand_id'));

            if ($mand) {
                if (PolicyVersion::get('ds_version') !== $mand->ds_version) {
                    $request->session()->put('_policy_update', 'ds');
                    return redirect()->route('mandant.policy.update');
                }

                if (PolicyVersion::get('upload_version') !== $mand->upload_terms_version) {
                    $request->session()->put('_policy_update', 'upload');
                    return redirect()->route('mandant.policy.update');
                }
            }
        }

        if ($userType === 'cust') {
            $cust = CustUser::find($request->session()->get('_cust_id'));

            if ($cust) {
                if (PolicyVersion::get('ds_version') !== $cust->ds_version) {
                    $request->session()->put('_policy_update', 'ds');
                    return redirect()->route('customer.policy.update');
                }
                // Upload-Bedingungen-Popup für cust entfernt (2026-07) — Inhalt für
                // cust nicht relevant, siehe PROJECT_CONTEXT.md. Hinweis stattdessen
                // statisch im Dashboard + FAQ.
            }
        }

        return $next($request);
    }
}
