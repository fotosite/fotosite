<?php
/**
 * FILE:        app/Http/Controllers/WelcomeScreenController.php
 * VERSION:     1.0.0
 *
 * ZWECK:       Blockierende Willkommensseiten für mand und cust beim ersten
 *              Login (show_welcome = 1), analog zu PolicyController. Wird
 *              von App\Http\Middleware\CheckWelcome angesteuert.
 *
 * FUNCTIONS:   showCust()    — Liest storage/app/private/willkommen_cust.md,
 *                               rendert via CommonMarkConverter, gibt
 *                               customer.welcome zurück.
 *                               Reads: storage/app/private/willkommen_cust.md
 *              showMand()    — Analog mit willkommen_mand.md, gibt
 *                               mandant.welcome zurück.
 *                               Reads: storage/app/private/willkommen_mand.md
 *              confirmCust() — Setzt cust_user.show_welcome = 0 für den
 *                               eingeloggten cust; Redirect zu customer.content.
 *                               Reads:  userdb.cust_user.cust_id
 *                               Writes: userdb.cust_user.show_welcome
 *              confirmMand() — Setzt mand_user.show_welcome = 0 für den
 *                               eingeloggten mand; Redirect zu mandant.dashboard.
 *                               Reads:  userdb.mand_user.mand_id
 *                               Writes: userdb.mand_user.show_welcome
 *
 * CALLS:       League\CommonMark\CommonMarkConverter::convert()
 *              App\Models\UserDb\CustUser::find()
 *              App\Models\UserDb\MandUser::find()
 *
 * DB ACCESS:   userdb.cust_user.cust_id, show_welcome
 *              userdb.mand_user.mand_id, show_welcome
 */

namespace App\Http\Controllers;

use App\Models\UserDb\CustUser;
use App\Models\UserDb\MandUser;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use League\CommonMark\CommonMarkConverter;

class WelcomeScreenController extends Controller
{
    public function showCust(): View
    {
        $html = $this->renderMarkdown('willkommen_cust.md');

        return view('customer.welcome', compact('html'));
    }

    public function showMand(): View
    {
        $html = $this->renderMarkdown('willkommen_mand.md');

        return view('mandant.welcome', compact('html'));
    }

    public function confirmCust(Request $request): RedirectResponse
    {
        $cust = CustUser::find($request->session()->get('_cust_id'));

        $cust?->update(['show_welcome' => 0]);

        return redirect()->route('customer.content');
    }

    public function confirmMand(Request $request): RedirectResponse
    {
        $mand = MandUser::find($request->session()->get('_mand_id'));

        $mand?->update(['show_welcome' => 0]);

        return redirect()->route('mandant.dashboard');
    }

    private function renderMarkdown(string $filename): string
    {
        $path = storage_path('app/private/'.$filename);

        if (! file_exists($path)) {
            abort(404, 'Willkommensdatei nicht gefunden.');
        }

        $converter = new CommonMarkConverter();

        return $converter->convert(file_get_contents($path))->getContent();
    }
}
