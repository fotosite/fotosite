<?php
/**
 * FILE:        app/Http/Middleware/CheckPflichtfelder.php
 * VERSION:     1.0.0
 * AUTOR:       Martin Wagner
 * DATUM:       2026-08-26
 *
 * ZWECK:       Erzwingt nach dem Login, dass cust/mand die aktuell laut
 *              storage/app/private/pflichtfelder.txt als Pflicht konfigurierten
 *              Felder (Telefon/Strasse/PLZOrt/Firma, siehe istPflichtfeld() in
 *              app/helpers.php) ausgefüllt haben. Fehlt eines dieser Felder
 *              (NULL in der DB): Redirect auf die Konto-Seite (customer.konto /
 *              mandant.konto) MIT withErrors() für genau die konkret fehlenden
 *              Felder — dadurch greifen dieselben @error('cust_tel')-Blöcke in
 *              customer/konto.blade.php/mandant/konto.blade.php wie bei einem
 *              fehlgeschlagenen Speichervorgang, keine separate Fehler-Anzeige
 *              nötig. Zusätzlich wird session('pflichtfeld_hinweis', true)
 *              gesetzt, damit eine erklärende Box angezeigt wird (siehe
 *              customer/konto.blade.php/mandant/konto.blade.php).
 *              Für cust wird zusätzlich session('pflichtfeld_redirect_target')
 *              auf die ursprünglich angeforderte URL gesetzt — CustSelfController
 *              ::update() leitet nach erfolgreichem Speichern dorthin zurück statt
 *              stur zur Konto-Seite. mand bekommt das bewusst NICHT gesetzt:
 *              mand landet nach dem Speichern immer auf der Konto-Seite und
 *              navigiert danach manuell über "Einstellungen" weiter (kein
 *              Sonderverhalten).
 *              anon/syst sind nicht betroffen, werden sofort durchgelassen.
 *              Ausnahmen (immer durchgelassen, auch bei fehlendem Pflichtfeld):
 *              die Konto-Seiten selbst und deren Speichern-Routen (sonst könnte
 *              sich der Nutzer nie retten), Logout-Routen (Nutzer muss sich
 *              immer abmelden können) und Datenschutz-Routen
 *              (routeIs('*.datenschutz.*'), identisches Muster wie
 *              CheckPolicyVersion/CheckWelcome).
 *              Läuft NACH CheckPolicyVersion und VOR CheckWelcome (siehe
 *              bootstrap/app.php) — eine veraltete Datenschutz-/Upload-Policy
 *              hat Vorrang vor fehlenden Pflichtangaben, fehlende Pflichtangaben
 *              wiederum Vorrang vor der reinen Onboarding-Willkommensseite.
 *              Die reine Ja/Nein-Prüfung (fehlt überhaupt ein Pflichtfeld) wird
 *              60 Sekunden gecacht (analog ValidateUserExists), damit nicht bei
 *              jedem Request erneut gegen 4 Config-Werte + DB geprüft wird. Die
 *              konkreten fehlenden Feldnamen für withErrors() werden dagegen
 *              IMMER frisch (ungecacht) ermittelt — aber nur in dem Moment, in
 *              dem tatsächlich umgeleitet wird, nicht bei jedem Request. Der
 *              Cache-Eintrag MUSS beim Speichern der Konto-Seite invalidiert
 *              werden (siehe CustSelfController::update()/
 *              MandantSelfController::update()), sonst bleibt der Nutzer nach
 *              dem Nachtragen der Daten für bis zu 60s fälschlich weiter gesperrt.
 *
 * FUNCTIONS:   handle(Request, Closure): Response
 *                  — Bricht sofort ab auf den Konto-Seiten/deren
 *                  Speichern-Routen, Logout-Routen und Datenschutz-Routen
 *                  (routeIs('*.datenschutz.*')) sowie ohne Session bzw. wenn
 *                  _user_type weder 'cust' noch 'mand' ist (anon/syst/nicht
 *                  eingeloggt). Prüft (gecacht, per missingFields() === []),
 *                  ob mindestens ein Pflichtfeld NULL ist. Falls nicht:
 *                  durchlassen. Falls doch: missingFields() erneut (ungecacht)
 *                  aufrufen, bei cust zusätzlich pflichtfeld_redirect_target
 *                  setzen, dann redirect()->route('customer.konto'|
 *                  'mandant.konto')->withErrors($missing)
 *                  ->with('pflichtfeld_hinweis', true).
 *              missingFields(string, int): array
 *                  — Lädt CustUser/MandUser, prüft für jedes der 4 Felder
 *                  istPflichtfeld(); ist ein Feld Pflicht UND in der DB NULL,
 *                  wird es mit MISSING_FIELD_MESSAGE ins Ergebnis-Array
 *                  aufgenommen (Feldname => Meldungstext, identischer Text für
 *                  alle vier Felder). Leeres Array = kein Feld fehlt.
 *
 * CALLS:       App\Models\UserDb\CustUser::find()
 *              App\Models\UserDb\MandUser::find()
 *              istPflichtfeld() (app/helpers.php)
 *              Illuminate\Support\Facades\Cache::remember()
 *
 * DB ACCESS:   userdb.cust_user.cust_id, cust_tel, cust_street+nr,
 *              cust_postcode_city, cust_company
 *              userdb.mand_user.mand_id, mand_tel, mand_street+nr,
 *              mand_postcode+city, mand_company
 *
 * CHANGES:     1.0.0 (2026-08-26) Erstversion.
 */

namespace App\Http\Middleware;

use App\Models\UserDb\CustUser;
use App\Models\UserDb\MandUser;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class CheckPflichtfelder
{
    private const MISSING_FIELD_MESSAGE = 'Dieses Feld ist mittlerweile ein Pflichtfeld – bitte ausfüllen.';

    private const FIELD_COLUMNS = [
        'cust' => [
            'Telefon' => 'cust_tel',
            'Strasse' => 'cust_street+nr',
            'PLZOrt'  => 'cust_postcode_city',
            'Firma'   => 'cust_company',
        ],
        'mand' => [
            'Telefon' => 'mand_tel',
            'Strasse' => 'mand_street+nr',
            'PLZOrt'  => 'mand_postcode+city',
            'Firma'   => 'mand_company',
        ],
    ];

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->routeIs('customer.konto')
            || $request->routeIs('customer.konto.update')
            || $request->routeIs('mandant.konto')
            || $request->routeIs('mandant.konto.update')
            || $request->routeIs('customer.logout')
            || $request->routeIs('mandant.logout')
            || $request->routeIs('*.datenschutz.*')) {
            return $next($request);
        }

        if (! $request->hasSession() || ! $request->session()->isStarted()) {
            return $next($request);
        }

        $userType = $request->session()->get('_user_type');

        if ($userType !== 'cust' && $userType !== 'mand') {
            return $next($request);
        }

        $userId = $request->session()->get($userType === 'cust' ? '_cust_id' : '_mand_id');

        if ($userId === null) {
            return $next($request);
        }

        $cacheKey = 'pflichtfelder_ok_' . $userType . '_' . $userId;

        $isOk = Cache::remember($cacheKey, 60, function () use ($userType, $userId) {
            return $this->missingFields($userType, $userId) === [];
        });

        if ($isOk) {
            return $next($request);
        }

        if ($userType === 'cust') {
            session(['pflichtfeld_redirect_target' => $request->fullUrl()]);
        }

        $kontoRoute = $userType === 'cust' ? 'customer.konto' : 'mandant.konto';

        return redirect()->route($kontoRoute)
            ->withErrors($this->missingFields($userType, $userId))
            ->with('pflichtfeld_hinweis', true);
    }

    private function missingFields(string $userType, int $userId): array
    {
        $model = $userType === 'cust' ? CustUser::find($userId) : MandUser::find($userId);

        if (! $model) {
            return [];
        }

        $missing = [];

        foreach (self::FIELD_COLUMNS[$userType] as $feldKey => $column) {
            if (! istPflichtfeld($userType, $feldKey)) {
                continue;
            }

            if ($model->{$column} === null) {
                $missing[$column] = self::MISSING_FIELD_MESSAGE;
            }
        }

        return $missing;
    }
}
