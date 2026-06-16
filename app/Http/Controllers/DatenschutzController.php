<?php
/**
 * FILE:        app/Http/Controllers/DatenschutzController.php
 * VERSION:     1.1.0
 *
 * FUNCTIONS:   erlaeuterung()         — Öffentlich (kein Auth-Check). Liest
 *                                       storage/app/private/erlaeuterung.md;
 *                                       filtert <!--MAND-->-Blöcke: mand-Session →
 *                                       Tags entfernen (Inhalt bleibt); sonst →
 *                                       gesamten Block entfernen. _user_type null
 *                                       wird wie non-mand behandelt.
 *                                       Wandelt Markdown zu HTML via CommonMarkConverter;
 *                                       gibt datenschutz.erlaeuterung zurück.
 *                                       Reads: storage/app/private/erlaeuterung.md
 *              erklaerungPdf()        — Öffentlich (kein Auth-Check). Liefert
 *                                       datenschutzerklaerung.pdf inline.
 *                                       Reads: storage/app/private/datenschutzerklaerung.pdf
 *              uploadBedingungenPdf() — Öffentlich (kein Auth-Check). Liefert
 *                                       upload_bedingungen.pdf inline.
 *                                       Reads: storage/app/private/upload_bedingungen.pdf
 *              hinweisOk()            — Setzt Session-Flag _ds_hinweis_gezeigt = true;
 *                                       Redirect zu customer.content.
 *                                       Writes: session._ds_hinweis_gezeigt
 *
 * CALLS:       League\CommonMark\CommonMarkConverter::convert()
 *
 * DB ACCESS:   —
 *
 * CHANGES:     1.1.0 (2026-06-16) requireSession() entfernt — erlaeuterung/PDF-Methoden
 *              sind öffentlich (Einladungsempfänger, anon ohne Login, cust, mand)
 */

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use League\CommonMark\CommonMarkConverter;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DatenschutzController extends Controller
{
    public function erlaeuterung(Request $request): View
    {
        $path = storage_path('app/private/erlaeuterung.md');

        if (! file_exists($path)) {
            abort(404, 'Erläuterungsdatei nicht gefunden.');
        }

        $md     = file_get_contents($path);
        $isMand = $request->session()->get('_user_type') === 'mand';

        if ($isMand) {
            $md = preg_replace('/<!--\s*\/?MAND\s*-->/', '', $md);
        } else {
            $md = preg_replace('/<!--\s*MAND\s*-->.*?<!--\s*\/MAND\s*-->/s', '', $md);
        }

        $converter = new CommonMarkConverter();
        $html      = $converter->convert($md)->getContent();

        return view('datenschutz.erlaeuterung', compact('html'));
    }

    public function erklaerungPdf(Request $request): BinaryFileResponse
    {
        $path = storage_path('app/private/datenschutzerklaerung.pdf');

        if (! file_exists($path)) {
            abort(404, 'Datei nicht gefunden.');
        }

        return response()->file($path, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="datenschutzerklaerung.pdf"',
        ]);
    }

    public function uploadBedingungenPdf(Request $request): BinaryFileResponse
    {
        $path = storage_path('app/private/upload_bedingungen.pdf');

        if (! file_exists($path)) {
            abort(404, 'Datei nicht gefunden.');
        }

        return response()->file($path, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="upload_bedingungen.pdf"',
        ]);
    }

    public function hinweisOk(Request $request): RedirectResponse
    {
        $request->session()->put('_ds_hinweis_gezeigt', true);

        return redirect()->route('customer.content');
    }
}
