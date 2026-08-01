<?php
/**
 * FILE:        app/Http/Controllers/SessionDb/MandantPwListController.php
 * VERSION:     1.10.0
 * AUTOR:       Martin Wagner
 * DATUM:       2026-08-01
 *
 * ZWECK:       pw_list Verwaltung — Passwortliste des Mandanten bearbeiten.
 *              pw1–pw6 werden verschlüsselt gespeichert (Laravel encrypt())
 *              und vor der Anzeige entschlüsselt (decrypt()).
 *
 * FUNCTIONS:   edit()   — Lädt PwList via _mand_id aus Session; entschlüsselt
 *                          pw1–pw6 vor der Übergabe an die View; ungültige
 *                          Cipher-Werte werden als '' behandelt. Ist die Liste
 *                          aktuell gültig (valid_from/valid_until), wird für
 *                          jede Stufe per firstOrCreate() ein persistenter
 *                          Kurzcode-Share-Link (share_link.code, 7-stellig)
 *                          angelegt/wiederverwendet; bei Code-Kollision (max.
 *                          5 Versuche) wird nur diese eine Stufe ausgelassen.
 *                          Reads: sessiondb.pw_list.*, sessiondb.share_link.*
 *                          Writes: sessiondb.share_link (firstOrCreate)
 *              update()        — Validiert pw1–pw6 (Klartext), prüft lokale Eindeutigkeit,
 *                                dann Kollisionsprüfung gegen aktive Listen anderer Mandanten
 *                                (lockForUpdate innerhalb DB-Transaktion); verschlüsselt
 *                                und speichert. Bei Kollision: RuntimeException → Rollback.
 *                                VOR dem Speichern wird pro Stufe der alte entschlüsselte
 *                                Wert mit dem neuen Klartext verglichen; bei Abweichung wird
 *                                der bestehende Share-Link dieser Stufe gelöscht (Erstanlage
 *                                zählt nicht als Änderung, da vorher kein Link existieren
 *                                konnte) — die nächste edit()-Anzeige erzeugt bei Bedarf
 *                                einen neuen Code.
 *                                Reads:  sessiondb.pw_list.mand_id, pw1–pw6, valid_until
 *                                Writes: sessiondb.pw_list.pw1–pw6, valid_from, valid_until
 *                                        sessiondb.share_link (DELETE je geänderter Stufe)
 *              checkPassword() — Prüft ob ein Passwort bereits in einer aktiven pw_list
 *                                eines anderen Mandanten vorkommt. Gibt JSON zurück.
 *                                Reads: sessiondb.pw_list.mand_id, pw1–pw6, valid_until
 *
 * CALLS:       App\Models\SessionDb\PwList::where()
 *              App\Models\SessionDb\PwList::updateOrCreate()
 *              App\Models\SessionDb\ShareLink::firstOrCreate()
 *              App\Models\SessionDb\ShareLink::where()->delete()
 *              encrypt() / decrypt()  — Laravel-Helpers (APP_KEY)
 *              Illuminate\Support\Facades\DB::connection('sessiondb')->transaction()
 *
 * DB ACCESS:   sessiondb.pw_list.pwlist_id, mand_id, pw1, pw2, pw3, pw4, pw5, pw6,
 *              valid_from, valid_until
 *              sessiondb.share_link.sl_id, code, mand_id, sec_level, created_at
 *
 * CHANGES:     1.10.0 (2026-08-01) Lange, verschlüsselte Share-Link-Tokens durch
 *              persistente 7-stellige Kurzcodes (sessiondb.share_link) ersetzt —
 *              siehe CustLoginController::loginViaShortCode(). edit() legt Codes
 *              per firstOrCreate() an (stabil über mehrere Seitenaufrufe hinweg,
 *              kein neuer Link bei jedem Reload). update() invalidiert gezielt
 *              nur die Share-Links der Stufen, deren Klartext sich geändert hat,
 *              statt bei jedem Speichern alle Stufen anzufassen.
 *              1.9.0 (2026-07-31) edit() — Share-Link-Tokens (encrypt('mand_id|level'))
 *              für anon-Login per teilbarem Link ergänzt, siehe
 *              CustLoginController::loginViaShareLink() und mandant/pwlist.blade.php.
 */

namespace App\Http\Controllers\SessionDb;

use App\Models\SessionDb\PwList;
use App\Models\SessionDb\ShareLink;
use Illuminate\Contracts\View\View;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class MandantPwListController extends SessionDbController
{
    public function edit(Request $request): View|RedirectResponse
    {
        $mandId = $request->session()->get('_mand_id');

        if (! $mandId) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('mandant.login');
        }

        $pwlist = PwList::where('mand_id', $mandId)->first();

        if ($pwlist) {
            foreach (['pw1', 'pw2', 'pw3', 'pw4', 'pw5', 'pw6'] as $field) {
                try {
                    $pwlist->$field = decrypt($pwlist->$field);
                } catch (\Exception $e) {
                    $pwlist->$field = '';
                }
            }
        }

        $shareLinks = [];

        if ($pwlist && $pwlist->valid_from <= now() && $pwlist->valid_until >= now()) {
            for ($level = 1; $level <= 6; $level++) {
                $shareLink = null;

                for ($attempt = 1; $attempt <= 5; $attempt++) {
                    try {
                        $shareLink = ShareLink::firstOrCreate(
                            ['mand_id' => $mandId, 'sec_level' => $level],
                            ['code' => Str::random(7), 'created_at' => now()]
                        );
                        break;
                    } catch (QueryException $e) {
                        continue;
                    }
                }

                if ($shareLink) {
                    $shareLinks[$level] = url('/s/' . $shareLink->code);
                } else {
                    Log::error('MandantPwListController::edit — Share-Link-Code-Kollision nach 5 Versuchen, Stufe ausgelassen.', [
                        'mand_id'   => $mandId,
                        'sec_level' => $level,
                    ]);
                }
            }
        }

        return view('mandant.pwlist', ['pwlist' => $pwlist, 'shareLinks' => $shareLinks]);
    }

    public function update(Request $request): RedirectResponse
    {
        $mandId = $request->session()->get('_mand_id');

        if (! $mandId) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('mandant.login');
        }

        $validator = Validator::make($request->all(), [
            'pw1'         => ['required', 'string', 'min:8'],
            'pw2'         => ['required', 'string', 'min:8'],
            'pw3'         => ['required', 'string', 'min:8'],
            'pw4'         => ['required', 'string', 'min:8'],
            'pw5'         => ['required', 'string', 'min:8'],
            'pw6'         => ['required', 'string', 'min:8'],
            'valid_from'  => ['required', 'date'],
            'valid_until' => ['required', 'date', 'after:valid_from', 'after:today'],
        ], [
            'pw1.required'        => 'Passwort 1 ist erforderlich.',
            'pw1.min'             => 'Passwort 1 muss mindestens 8 Zeichen haben.',
            'pw2.required'        => 'Passwort 2 ist erforderlich.',
            'pw2.min'             => 'Passwort 2 muss mindestens 8 Zeichen haben.',
            'pw3.required'        => 'Passwort 3 ist erforderlich.',
            'pw3.min'             => 'Passwort 3 muss mindestens 8 Zeichen haben.',
            'pw4.required'        => 'Passwort 4 ist erforderlich.',
            'pw4.min'             => 'Passwort 4 muss mindestens 8 Zeichen haben.',
            'pw5.required'        => 'Passwort 5 ist erforderlich.',
            'pw5.min'             => 'Passwort 5 muss mindestens 8 Zeichen haben.',
            'pw6.required'        => 'Passwort 6 ist erforderlich.',
            'pw6.min'             => 'Passwort 6 muss mindestens 8 Zeichen haben.',
            'valid_from.required' => 'Bitte Gültigkeitsbeginn angeben.',
            'valid_from.date'     => 'Gültigkeitsbeginn muss ein gültiges Datum sein.',
            'valid_until.required'=> 'Bitte Ablaufdatum angeben.',
            'valid_until.date'    => 'Ablaufdatum muss ein gültiges Datum sein.',
            'valid_until.after'   => 'Das Ablaufdatum muss nach dem Gültigkeitsbeginn und in der Zukunft liegen.',
        ]);

        if ($validator->fails()) {
            return back()
                ->withInput()
                ->withErrors($validator)
                ->with('error', 'Nicht gespeichert — Fehler liegen vor.');
        }

        $validated = $validator->validated();

        $passwords = [$validated['pw1'], $validated['pw2'], $validated['pw3'],
                      $validated['pw4'], $validated['pw5'], $validated['pw6']];

        if (count($passwords) !== count(array_unique($passwords))) {
            return back()
                ->withInput()
                ->withErrors(['passwords' => 'Die Passwörter müssen eindeutig sein.'])
                ->with('error', 'Nicht gespeichert — Fehler liegen vor.');
        }

        // ALTE Zeile VOR dem Überschreiben laden, um pro Stufe zu erkennen, ob
        // sich der Klartext geändert hat (Reihenfolge kritisch — muss vor dem
        // Update-Call bekannt sein). Existierte vorher keine Zeile, zählt keine
        // Stufe als "geändert" (Erstanlage — vorher konnte kein Link existieren).
        $oldPwlist     = PwList::where('mand_id', $mandId)->first();
        $changedLevels = [];

        if ($oldPwlist) {
            foreach (['pw1', 'pw2', 'pw3', 'pw4', 'pw5', 'pw6'] as $index => $field) {
                try {
                    $oldValue = decrypt($oldPwlist->$field);
                } catch (\Exception $e) {
                    $oldValue = null;
                }

                if ($oldValue !== $validated[$field]) {
                    $changedLevels[] = $index + 1;
                }
            }
        }

        try {
            DB::connection('sessiondb')->transaction(function () use ($mandId, $validated, $changedLevels) {

                $activeLists = PwList::where('mand_id', '!=', $mandId)
                    ->where('valid_until', '>=', now())
                    ->lockForUpdate()
                    ->get();

                $myPasswords = [
                    $validated['pw1'], $validated['pw2'],
                    $validated['pw3'], $validated['pw4'],
                    $validated['pw5'], $validated['pw6'],
                ];

                foreach ($activeLists as $list) {
                    foreach (['pw1', 'pw2', 'pw3', 'pw4', 'pw5', 'pw6'] as $field) {
                        try {
                            $existing = decrypt($list->$field);
                        } catch (\Exception $e) {
                            continue;
                        }
                        if (in_array($existing, $myPasswords, true)) {
                            throw new \RuntimeException(
                                'Passwort bereits von einem anderen Galerist:in vergeben: ' . $field
                            );
                        }
                    }
                }

                PwList::updateOrCreate(['mand_id' => $mandId], [
                    'pw1'         => encrypt($validated['pw1']),
                    'pw2'         => encrypt($validated['pw2']),
                    'pw3'         => encrypt($validated['pw3']),
                    'pw4'         => encrypt($validated['pw4']),
                    'pw5'         => encrypt($validated['pw5']),
                    'pw6'         => encrypt($validated['pw6']),
                    'valid_from'  => $validated['valid_from'],
                    'valid_until' => $validated['valid_until'],
                ]);

                foreach ($changedLevels as $level) {
                    ShareLink::where('mand_id', $mandId)->where('sec_level', $level)->delete();
                }
            });

            return redirect()->back()
                ->with('status', 'Passwortliste gespeichert.');

        } catch (\RuntimeException $e) {
            return back()
                ->withInput()
                ->withErrors(['passwords' => $e->getMessage()])
                ->with('error', 'Nicht gespeichert — Kollision mit anderem Galerist:in.');
        }
    }

    public function checkPassword(Request $request): JsonResponse
    {
        $mandId = $request->session()->get('_mand_id');

        if (! $mandId) {
            return response()->json(['available' => false, 'message' => '']);
        }

        $pw = $request->input('password', '');

        if (strlen($pw) < 8) {
            return response()->json(['available' => true, 'message' => '']);
        }

        $lists = PwList::where('mand_id', '!=', $mandId)
            ->where('valid_until', '>=', now())
            ->get();

        foreach ($lists as $list) {
            foreach (['pw1', 'pw2', 'pw3', 'pw4', 'pw5', 'pw6'] as $field) {
                try {
                    if (decrypt($list->$field) === $pw) {
                        return response()->json([
                            'available' => false,
                            'message'   => 'Passwort bereits vergeben.',
                        ]);
                    }
                } catch (\Exception) {
                    // Ungültiger Cipher — überspringen
                }
            }
        }

        return response()->json(['available' => true, 'message' => '']);
    }
}
