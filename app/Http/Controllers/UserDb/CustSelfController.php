<?php
/**
 * FILE:        app/Http/Controllers/UserDb/CustSelfController.php
 * VERSION:     1.2.0
 * AUTOR:       Martin Wagner
 * DATUM:       2026-06-13
 *
 * ZWECK:       Customer Eigenverwaltung — Kontaktdaten, Passwort, Galerien verwalten,
 *              Konto löschen.
 *
 * FUNCTIONS:   edit()              — Lädt CustUser via _cust_id aus Session;
 *                                     gibt customer.konto mit $cust zurück.
 *                                     Reads: userdb.cust_user.cust_id, alle Felder
 *              update()            — Validiert und speichert Kontaktdaten.
 *                                     Reads:  userdb.cust_user.cust_id
 *                                     Writes: userdb.cust_user.cust_email, cust_firstname,
 *                                             cust_lastname, cust_tel, cust_street+nr,
 *                                             cust_postcode_city, cust_company
 *              updatePassword()    — Validiert aktuelles + neues Passwort (Policy: min 10
 *                                     Zeichen); prüft ob Benutzername im Passwort enthalten
 *                                     ist; speichert neuen Hash; invalidiert Session;
 *                                     Redirect zu home mit Login-Modal-Hint.
 *                                     Reads:  userdb.cust_user.cust_id, cust_uname,
 *                                             cust_pw_hash
 *                                     Writes: userdb.cust_user.cust_pw_hash
 *              galerien()          — Lädt alle cust_pcode-Einträge des Cust mit mandUser;
 *                                     gibt customer.galerien zurück.
 *                                     Reads: userdb.cust_pcode.*, userdb.mand_user.*
 *              reorderGalerie()    — Tauscht pcode_prefstat zweier benachbarter Einträge
 *                                     (up/down); weist sequenzielle Werte zu.
 *                                     Reads/Writes: userdb.cust_pcode.pcode_prefstat
 *              saveSettings()      — Setzt cust_mailrequest für alle pcode-Einträge des
 *                                     Cust auf Basis der übermittelten Checkboxen;
 *                                     Checkbox-Fehlende → false (unchecked sendet nichts).
 *                                     Reads:  userdb.cust_pcode.pcode_id
 *                                     Writes: userdb.cust_pcode.cust_mailrequest
 *              removeGalerie()     — Löscht einen pcode-Eintrag; bei letztem Eintrag:
 *                                     destroyAccount(). Redirect zu galerien oder home.
 *                                     Reads:  userdb.cust_pcode.pcode_id, cust_id
 *                                     Deletes: userdb.cust_pcode (ggf. passkey,
 *                                              passkey_dismissed, cust_user via helper)
 *              deleteAccount()     — Löscht das gesamte Konto via destroyAccount();
 *                                     invalidiert Session; Redirect zu home.
 *                                     Deletes: userdb.passkey, passkey_dismissed,
 *                                              cust_pcode, cust_user
 *              destroyAccount()    — Private Helper: löscht passkey, passkey_dismissed,
 *                                     alle cust_pcode-Einträge und cust_user für $custId.
 *
 * CALLS:       App\Models\UserDb\CustUser::find()
 *              App\Models\UserDb\CustPcode::where()
 *              App\Models\UserDb\Passkey::where()
 *              App\Models\UserDb\PasskeyDismissed::where()
 *              Illuminate\Support\Facades\Hash::check()
 *              Illuminate\Support\Facades\Hash::make()
 *              Illuminate\Validation\Rules\Password::min()
 *
 * DB ACCESS:   userdb.cust_user.cust_id, cust_uname, cust_email, cust_tel,
 *              cust_firstname, cust_lastname, cust_street+nr, cust_postcode_city,
 *              cust_company, cust_pw_hash
 *              userdb.cust_pcode.pcode_id, cust_id, mand_id, pcode_prefstat,
 *              cust_mailrequest (READ/WRITE/DELETE)
 *              userdb.passkey.user_type, user_id (DELETE)
 *              userdb.passkey_dismissed.user_type, user_id (DELETE)
 */

namespace App\Http\Controllers\UserDb;

use App\Models\UserDb\CustPcode;
use App\Models\UserDb\CustUser;
use App\Models\UserDb\Passkey;
use App\Models\UserDb\PasskeyDismissed;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class CustSelfController extends UserDbController
{
    public function edit(Request $request): View|RedirectResponse
    {
        $custId = $request->session()->get('_cust_id');
        $cust   = $custId ? CustUser::find($custId) : null;

        if (! $cust) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('customer.login');
        }

        return view('customer.konto', compact('cust'));
    }

    public function update(Request $request): RedirectResponse
    {
        $custId = $request->session()->get('_cust_id');
        $cust   = $custId ? CustUser::find($custId) : null;

        if (! $cust) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('customer.login');
        }

        $validated = $request->validate([
            'cust_email'         => ['required', 'email', 'max:255', "unique:userdb.cust_user,cust_email,{$custId},cust_id"],
            'cust_firstname'     => ['nullable', 'string', 'max:255'],
            'cust_lastname'      => ['nullable', 'string', 'max:255'],
            'cust_tel'           => ['nullable', 'string', 'max:255'],
            'cust_street+nr'     => ['nullable', 'string', 'max:255'],
            'cust_postcode_city' => ['nullable', 'string', 'max:255'],
            'cust_company'       => ['nullable', 'string', 'max:255'],
        ]);

        $cust->update($validated);

        return redirect()->route('customer.konto')
            ->with('status', 'Kontaktdaten gespeichert.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $custId = $request->session()->get('_cust_id');
        $cust   = $custId ? CustUser::find($custId) : null;

        if (! $cust) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('customer.login');
        }

        $request->validate([
            'current_password' => ['required'],
            'password'         => [
                'required',
                'confirmed',
                Password::min(10),
            ],
        ]);

        if ($cust->cust_uname && str_contains($request->password, $cust->cust_uname)) {
            return back()->withErrors(['password' => 'Das Passwort darf den Benutzernamen nicht enthalten.']);
        }

        if (! Hash::check($request->current_password, $cust->cust_pw_hash)) {
            return back()->withErrors(['current_password' => 'Das aktuelle Passwort ist falsch.']);
        }

        $cust->update(['cust_pw_hash' => Hash::make($request->password)]);

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')
            ->with('status', 'Passwort geändert. Bitte melden Sie sich erneut an.')
            ->with('open_login_modal', 'cust');
    }

    public function galerien(Request $request): View|RedirectResponse
    {
        $custId = $request->session()->get('_cust_id');
        $cust   = $custId ? CustUser::find($custId) : null;

        if (! $cust) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('customer.login');
        }

        $pcodes = CustPcode::where('cust_id', $custId)
            ->orderBy('pcode_prefstat')
            ->orderBy('pcode_id')
            ->with('mandUser')
            ->get();

        return view('customer.galerien', compact('pcodes'));
    }

    public function reorderGalerie(Request $request, int $pcodeId, string $direction): RedirectResponse
    {
        $custId = $request->session()->get('_cust_id');
        $cust   = $custId ? CustUser::find($custId) : null;

        if (! $cust) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('customer.login');
        }

        $pcodes = CustPcode::where('cust_id', $custId)
            ->orderBy('pcode_prefstat')
            ->orderBy('pcode_id')
            ->get();

        $index = $pcodes->search(fn($p) => $p->pcode_id === $pcodeId);
        if ($index === false) {
            return redirect()->route('customer.galerien');
        }

        $swapIndex = $direction === 'up' ? $index - 1 : $index + 1;
        if ($swapIndex < 0 || $swapIndex >= $pcodes->count()) {
            return redirect()->route('customer.galerien');
        }

        // Reorder collection and assign sequential pcode_prefstat values
        $items = $pcodes->values()->all();
        [$items[$index], $items[$swapIndex]] = [$items[$swapIndex], $items[$index]];

        foreach ($items as $i => $pcode) {
            if ($pcode->pcode_prefstat !== $i) {
                $pcode->update(['pcode_prefstat' => $i]);
            }
        }

        return redirect()->route('customer.galerien');
    }

    public function saveSettings(Request $request): RedirectResponse
    {
        $custId = $request->session()->get('_cust_id');
        $cust   = $custId ? CustUser::find($custId) : null;

        if (! $cust) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('customer.login');
        }

        $pcodes = CustPcode::where('cust_id', $custId)->get();

        foreach ($pcodes as $pcode) {
            $pcode->cust_mailrequest = $request->has("mailrequest_{$pcode->pcode_id}");
            $pcode->save();
        }

        return redirect()->route('customer.galerien')
            ->with('status', 'Einstellungen gespeichert.');
    }

    public function removeGalerie(Request $request, int $pcodeId): RedirectResponse
    {
        $custId = $request->session()->get('_cust_id');
        $cust   = $custId ? CustUser::find($custId) : null;

        if (! $cust) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('customer.login');
        }

        $pcodes = CustPcode::where('cust_id', $custId)->get();
        $target = $pcodes->firstWhere('pcode_id', $pcodeId);

        if (! $target) {
            abort(404);
        }

        if ($pcodes->count() === 1) {
            $this->destroyAccount($custId);
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('home')
                ->with('status', 'Ihr Konto wurde gelöscht.');
        }

        $target->delete();

        return redirect()->route('customer.galerien')
            ->with('status', 'Galerie entfernt.');
    }

    public function deleteAccount(Request $request): RedirectResponse
    {
        $custId = $request->session()->get('_cust_id');
        $cust   = $custId ? CustUser::find($custId) : null;

        if (! $cust) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('customer.login');
        }

        $this->destroyAccount($custId);

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')
            ->with('status', 'Ihr Konto wurde gelöscht.');
    }

    private function destroyAccount(int $custId): void
    {
        Passkey::where('user_type', 'cust')->where('user_id', $custId)->delete();
        PasskeyDismissed::where('user_type', 'cust')->where('user_id', $custId)->delete();
        CustPcode::where('cust_id', $custId)->delete();
        CustUser::find($custId)?->delete();
    }
}
