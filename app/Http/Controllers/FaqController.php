<?php
/**
 * FILE:        app/Http/Controllers/FaqController.php
 * VERSION:     1.0.0
 *
 * ZWECK:       FAQ & Infos — dynamische, dateibasierte Liste ohne DB-Beteiligung.
 *              Scannt storage/app/private/faq/{cust,mand}/*.md, zeigt pro Datei
 *              einen Button; Klick lädt den gerenderten Markdown-Inhalt per AJAX
 *              in ein Modal. Neue .md-Dateien erscheinen automatisch ohne
 *              Code-Änderung. Markdown-Rendering analog DatenschutzController
 *              (CommonMarkConverter).
 *
 * FUNCTIONS:   indexCust()        — Liste für customer.faq.index.
 *                                    Reads: Dateinamen storage/app/private/faq/cust/*.md
 *              indexMand()        — Liste für mandant.faq.index.
 *                                    Reads: Dateinamen storage/app/private/faq/mand/*.md
 *              showCust($slug)    — JSON {title, html} für AJAX-Modal (cust).
 *                                    Reads: storage/app/private/faq/cust/{slug}.md
 *              showMand($slug)    — JSON {title, html} für AJAX-Modal (mand).
 *                                    Reads: storage/app/private/faq/mand/{slug}.md
 *
 * CALLS:       League\CommonMark\CommonMarkConverter::convert()
 *
 * DB ACCESS:   — (keine, reines Dateisystem-Feature)
 *
 * SICHERHEIT:  $slug wird ausschließlich über buildSafePath() in einen
 *              Dateipfad umgewandelt — Whitelist-Regex (nur a-z, A-Z, 0-9,
 *              "_", "-") + basename(), kein direkter String-Concat aus
 *              Nutzereingabe. Bricht bei ungültigem Slug mit 404 ab statt
 *              einen Pfad außerhalb des FAQ-Ordners zu öffnen.
 */

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Contracts\View\View;
use League\CommonMark\CommonMarkConverter;

class FaqController extends Controller
{
    public function indexCust(): View
    {
        return view('faq.index_cust', ['items' => $this->listFiles('cust')]);
    }

    public function indexMand(): View
    {
        return view('faq.index_mand', ['items' => $this->listFiles('mand')]);
    }

    public function showCust(string $slug): JsonResponse
    {
        return $this->renderSlug('cust', $slug);
    }

    public function showMand(string $slug): JsonResponse
    {
        return $this->renderSlug('mand', $slug);
    }

    /**
     * Listet alle *.md-Dateien im rollenspezifischen FAQ-Ordner, alphabetisch
     * (case-insensitive) nach Dateiname sortiert. Fehlender/leerer Ordner
     * führt zu einer leeren Liste, NICHT zu einem Fehler.
     */
    private function listFiles(string $role): array
    {
        $dir = storage_path('app/private/faq/'.$role);

        if (! is_dir($dir)) {
            return [];
        }

        $files = glob($dir.'/*.md') ?: [];

        $items = array_map(function (string $path) {
            $slug = basename($path, '.md');

            return [
                'slug'  => $slug,
                'label' => str_replace('_', ' ', $slug),
            ];
        }, $files);

        usort($items, fn ($a, $b) => strcasecmp($a['slug'], $b['slug']));

        return $items;
    }

    /**
     * Validiert $slug gegen Path-Traversal (Whitelist-Regex + basename())
     * und rendert die zugehörige .md-Datei zu HTML.
     */
    private function renderSlug(string $role, string $slug): JsonResponse
    {
        $path = $this->buildSafePath($role, $slug);

        if ($path === null || ! is_file($path)) {
            abort(404, 'FAQ-Eintrag nicht gefunden.');
        }

        $converter = new CommonMarkConverter();
        $html      = $converter->convert(file_get_contents($path))->getContent();

        return response()->json([
            'title' => str_replace('_', ' ', basename($path, '.md')),
            'html'  => $html,
        ]);
    }

    /**
     * Baut einen sicheren Dateipfad aus $role (fest, kein User-Input) und
     * $slug (User-Input). $slug muss ausschließlich aus a-z, A-Z, 0-9, "_"
     * und "-" bestehen (kein "/", kein "..", kein Null-Byte) UND wird
     * zusätzlich durch basename() geschickt, um jeden verbleibenden
     * Pfadanteil zu entfernen. Bei Verstoß: null (Aufrufer bricht mit 404 ab).
     */
    private function buildSafePath(string $role, string $slug): ?string
    {
        if (! preg_match('/^[a-zA-Z0-9_\-]+$/', $slug)) {
            return null;
        }

        $safeSlug = basename($slug);

        if ($safeSlug !== $slug) {
            return null;
        }

        return storage_path('app/private/faq/'.$role.'/'.$safeSlug.'.md');
    }
}
