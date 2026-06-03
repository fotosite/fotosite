<?php
/**
 * FILE:        app/Http/Controllers/SessionDb/MandantPwListController.php
 * VERSION:     1.6.0
 * AUTOR:       Martin Wagner
 * DATUM:       2026-05-30
 *
 * ZWECK:       pw_list Verwaltung — Passwortliste des Mandanten bearbeiten.
 *              pw1–pw6 werden verschlüsselt gespeichert (Laravel encrypt())
 *              und vor der Anzeige entschlüsselt (decrypt()).
 *
 * FUNCTIONS:   edit()   — Lädt PwList via _mand_id aus Session; entschlüsselt
 *                          pw1–pw6 vor der Übergabe an die View; ungültige
 *                          Cipher-Werte werden als '' behandelt.
 *                          Reads: sessiondb.pw_list.*
 *              update() — Validiert pw1–pw6 (Klartext), prüft Eindeutigkeit,
 *                          verschlüsselt sie vor dem Speichern; schreibt valid_from, valid_until.
 *                          Writes: sessiondb.pw_list.pw1–pw6, valid_from, valid_until
 *
 * CALLS:       App\Models\SessionDb\PwList::where()
 *              App\Models\SessionDb\PwList::updateOrCreate()
 *              encrypt() / decrypt()  — Laravel-Helpers (APP_KEY)
 *
 * DB ACCESS:   sessiondb.pw_list.pwlist_id, mand_id, pw1, pw2, pw3, pw4, pw5, pw6,
 *              valid_from, valid_until
 */

namespace App\Http\Controllers\SessionDb;

use App\Models\SessionDb\PwList;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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

        return view('mandant.pwlist', ['pwlist' => $pwlist]);
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

        return redirect()->back()
            ->with('status', 'Passwortliste gespeichert.');
    }
}
