<?php
/**
 * FILE:        app/Http/Controllers/SessionDb/MandantPwListController.php
 * VERSION:     1.0.0
 * AUTOR:       Martin Wagner
 * DATUM:       2026-05-30
 *
 * ZWECK:       pw_list Verwaltung — Passwortliste des Mandanten bearbeiten.
 *
 * FUNCTIONS:   edit()   — Lädt PwList via _mand_id aus Session;
 *                          gibt mandant.pwlist mit $pwlist zurück.
 *                          Reads: sessiondb.pw_list.*
 *              update() — Validiert und speichert pw1–pw6, valid_from, valid_until.
 *                          Writes: sessiondb.pw_list.pw1–pw6, valid_from, valid_until
 *
 * CALLS:       App\Models\SessionDb\PwList::where()
 *              App\Models\SessionDb\PwList::updateOrCreate()
 *
 * DB ACCESS:   sessiondb.pw_list.pwlist_id, mand_id, pw1, pw2, pw3, pw4, pw5, pw6,
 *              valid_from, valid_until
 */

namespace App\Http\Controllers\SessionDb;

use App\Models\SessionDb\PwList;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

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

        $validated = $request->validate([
            'pw1'         => ['required', 'string', 'min:8'],
            'pw2'         => ['required', 'string', 'min:8'],
            'pw3'         => ['required', 'string', 'min:8'],
            'pw4'         => ['required', 'string', 'min:8'],
            'pw5'         => ['required', 'string', 'min:8'],
            'pw6'         => ['required', 'string', 'min:8'],
            'valid_from'  => ['required', 'date'],
            'valid_until' => ['required', 'date', 'after:valid_from'],
        ]);

        PwList::updateOrCreate(['mand_id' => $mandId], $validated);

        return redirect()->back()
            ->with('status', 'Passwortliste gespeichert.');
    }
}
