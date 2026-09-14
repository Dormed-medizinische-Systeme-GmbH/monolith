<?php

declare(strict_types=1);

use App\Http\Controllers\Website\ContactFormController;
use App\Support\AccessPoint;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| dormed.de — oeffentlicher Auftritt (ADR-038)
|--------------------------------------------------------------------------
|
| Anonym, Datenbankrolle `dormed_public`, Frontend Blade (ADR-039).
| Keine Inertia-Seiten hier — die Website wird von Suchmaschinen gelesen,
| nicht bedient.
|
| 1:1 aus dem Altprojekt uebernommen (`.legacy/dormed.de`), mit zwei
| Anpassungen:
|
|   * View-Namen tragen das Praefix `website.`, weil die Views jetzt unter
|     `resources/views/website/` liegen. Die Blade-Komponenten (`<x-layout>`)
|     blieben auf Wurzelebene und funktionieren dadurch unveraendert.
|   * Route-NAMEN tragen dasselbe Praefix. Vier Zugriffspunkte teilen sich in
|     einem Monolithen ein Namensregister; ohne Praefix wuerde ein spaeteres
|     `kontakt` im Shop das der Website stillschweigend ueberschreiben.
|
| Die URL-Pfade sind unveraendert — kein Redirect, kein SEO-Verlust.
|
*/

/*
 * Sitemaps laufen ueber eine Route, nicht als statische Datei in public/:
 * der Inhalt ist aktuell 1:1 die alte Datei, laesst sich aber spaeter ohne
 * URL-Aenderung dynamisch erzeugen.
 */
Route::get('/sitemap.xml', function () {
    return response(file_get_contents(resource_path('sitemap/sitemap.xml')))
        ->header('Content-Type', 'application/xml');
})->name('website.sitemap');

Route::get('/sitemap-system-pages.xml', function () {
    return response(file_get_contents(resource_path('sitemap/sitemap-system-pages.xml')))
        ->header('Content-Type', 'application/xml');
})->name('website.sitemap.system-pages');

/*
 * KI-Crawler-Alternative je Produktseite: /ultraschallgeraete/{...}.md liefert
 * die Markdown-Datei aus, die direkt neben der zugehoerigen Blade-View liegt.
 * Existiert nur fuer Produkte mit Kurzprofil, sonst 404.
 */
Route::get('/ultraschallgeraete/{path}.md', function (string $path) {
    $file = resource_path("views/website/ultraschallgeraete/{$path}.md");

    abort_unless(is_file($file), 404);

    return response(file_get_contents($file))
        ->header('Content-Type', 'text/markdown; charset=UTF-8');
})->where('path', '[a-z0-9\-\/]+')->name('website.ultraschallgeraete.markdown');

Route::post('/kontakt', [ContactFormController::class, 'store'])
    ->middleware('throttle:3,1')
    ->name('website.kontakt.store');

Route::view('/', 'website.index')->name('website.index');
Route::view('/blog', 'website.blog.index')->name('website.blog.index');
Route::view('/danke', 'website.danke')->name('website.danke');
Route::view('/fuer', 'website.fuer.index')->name('website.fuer.index');
Route::view('/fuer/allgemeinmedizin', 'website.fuer.allgemeinmedizin.index')->name('website.fuer.allgemeinmedizin.index');
Route::view('/fuer/allgemeinmedizin/leber-elastographie', 'website.fuer.allgemeinmedizin.leber-elastographie')->name('website.fuer.allgemeinmedizin.leber-elastographie');
Route::view('/fuer/allgemeinmedizin/schilddruesen-sonographie', 'website.fuer.allgemeinmedizin.schilddruesen-sonographie')->name('website.fuer.allgemeinmedizin.schilddruesen-sonographie');
Route::view('/fuer/allgemeinmedizin/sonographie', 'website.fuer.allgemeinmedizin.sonographie')->name('website.fuer.allgemeinmedizin.sonographie');
Route::view('/fuer/gynaekologie', 'website.fuer.gynaekologie.index')->name('website.fuer.gynaekologie.index');
Route::view('/fuer/gynaekologie/vaginaler-ultraschall', 'website.fuer.gynaekologie.vaginaler-ultraschall')->name('website.fuer.gynaekologie.vaginaler-ultraschall');
Route::view('/fuer/kardiologie', 'website.fuer.kardiologie.index')->name('website.fuer.kardiologie.index');
Route::view('/fuer/kardiologie/cw-doppler', 'website.fuer.kardiologie.cw-doppler')->name('website.fuer.kardiologie.cw-doppler');
Route::view('/fuer/kardiologie/echokardiographie', 'website.fuer.kardiologie.echokardiographie')->name('website.fuer.kardiologie.echokardiographie');
Route::view('/fuer/kardiologie/farbduplexsonographie', 'website.fuer.kardiologie.farbduplexsonographie')->name('website.fuer.kardiologie.farbduplexsonographie');
Route::view('/fuer/kardiologie/pw-doppler', 'website.fuer.kardiologie.pw-doppler')->name('website.fuer.kardiologie.pw-doppler');
Route::view('/fuer/kardiologie/wirtschaftlichkeit', 'website.fuer.kardiologie.wirtschaftlichkeit')->name('website.fuer.kardiologie.wirtschaftlichkeit');
Route::view('/fuer/orthopaedie', 'website.fuer.orthopaedie')->name('website.fuer.orthopaedie');
Route::view('/hersteller', 'website.hersteller.index')->name('website.hersteller.index');
Route::view('/hersteller/chison', 'website.hersteller.chison')->name('website.hersteller.chison');
Route::view('/hersteller/esaote', 'website.hersteller.esaote')->name('website.hersteller.esaote');
Route::view('/hersteller/mindray', 'website.hersteller.mindray')->name('website.hersteller.mindray');
Route::view('/karriere', 'website.karriere')->name('website.karriere');
Route::view('/kontakt', 'website.kontakt')->name('website.kontakt');
Route::view('/leistungen', 'website.leistungen.index')->name('website.leistungen.index');
Route::view('/leistungen/beratung', 'website.leistungen.beratung')->name('website.leistungen.beratung');
Route::view('/leistungen/finanzierung', 'website.leistungen.finanzierung')->name('website.leistungen.finanzierung');
Route::view('/leistungen/garantie-versicherung', 'website.leistungen.garantie-versicherung')->name('website.leistungen.garantie-versicherung');
Route::view('/leistungen/inzahlungnahme', 'website.leistungen.inzahlungnahme')->name('website.leistungen.inzahlungnahme');
Route::view('/leistungen/lieferung', 'website.leistungen.lieferung')->name('website.leistungen.lieferung');
Route::view('/leistungen/netzwerkanbindung', 'website.leistungen.netzwerkanbindung')->name('website.leistungen.netzwerkanbindung');
Route::view('/leistungen/schulung-einweisung', 'website.leistungen.schulung-einweisung')->name('website.leistungen.schulung-einweisung');
Route::view('/leistungen/wartung-reparatur', 'website.leistungen.wartung-reparatur')->name('website.leistungen.wartung-reparatur');
Route::view('/standorte', 'website.standorte.index')->name('website.standorte.index');
Route::view('/standorte/digitale-sonothek', 'website.standorte.digitale-sonothek')->name('website.standorte.digitale-sonothek');
Route::view('/standorte/dortmund', 'website.standorte.dortmund')->name('website.standorte.dortmund');
Route::view('/standorte/duesseldorf', 'website.standorte.duesseldorf')->name('website.standorte.duesseldorf');
Route::view('/standorte/hamburg', 'website.standorte.hamburg')->name('website.standorte.hamburg');
Route::view('/standorte/kiel', 'website.standorte.kiel')->name('website.standorte.kiel');
Route::view('/ueber', 'website.ueber.index')->name('website.ueber.index');
Route::view('/ueber/dormed', 'website.ueber.dormed')->name('website.ueber.dormed');
Route::view('/ueber/sonoring', 'website.ueber.sonoring')->name('website.ueber.sonoring');
Route::view('/ultraschallgeraete', 'website.ultraschallgeraete.index')->name('website.ultraschallgeraete.index');
Route::view('/ultraschallgeraete/gebraucht', 'website.ultraschallgeraete.gebraucht')->name('website.ultraschallgeraete.gebraucht');
Route::view('/ultraschallgeraete/handheld', 'website.ultraschallgeraete.handheld.index')->name('website.ultraschallgeraete.handheld.index');
Route::view('/ultraschallgeraete/handheld/mindray-te-air-e5m', 'website.ultraschallgeraete.handheld.mindray-te-air-e5m')->name('website.ultraschallgeraete.handheld.mindray-te-air-e5m');
Route::view('/ultraschallgeraete/handheld/mindray-te-air-i3m', 'website.ultraschallgeraete.handheld.mindray-te-air-i3m')->name('website.ultraschallgeraete.handheld.mindray-te-air-i3m');
Route::view('/ultraschallgeraete/mobile-geraete', 'website.ultraschallgeraete.mobile-geraete.index')->name('website.ultraschallgeraete.mobile-geraete.index');
Route::view('/ultraschallgeraete/mobile-geraete/chison-sonoair-70', 'website.ultraschallgeraete.mobile-geraete.chison-sonoair-70')->name('website.ultraschallgeraete.mobile-geraete.chison-sonoair-70');
Route::view('/ultraschallgeraete/mobile-geraete/esaote-mylab-c25', 'website.ultraschallgeraete.mobile-geraete.esaote-mylab-c25')->name('website.ultraschallgeraete.mobile-geraete.esaote-mylab-c25');
Route::view('/ultraschallgeraete/mobile-geraete/esaote-mylab-x1-go', 'website.ultraschallgeraete.mobile-geraete.esaote-mylab-x1-go')->name('website.ultraschallgeraete.mobile-geraete.esaote-mylab-x1-go');
Route::view('/ultraschallgeraete/mobile-geraete/mindray-dp-10', 'website.ultraschallgeraete.mobile-geraete.mindray-dp-10')->name('website.ultraschallgeraete.mobile-geraete.mindray-dp-10');
Route::view('/ultraschallgeraete/mobile-geraete/mindray-dp-30', 'website.ultraschallgeraete.mobile-geraete.mindray-dp-30')->name('website.ultraschallgeraete.mobile-geraete.mindray-dp-30');
Route::view('/ultraschallgeraete/mobile-geraete/mindray-dp-50', 'website.ultraschallgeraete.mobile-geraete.mindray-dp-50')->name('website.ultraschallgeraete.mobile-geraete.mindray-dp-50');
Route::view('/ultraschallgeraete/mobile-geraete/mindray-dp-60', 'website.ultraschallgeraete.mobile-geraete.mindray-dp-60')->name('website.ultraschallgeraete.mobile-geraete.mindray-dp-60');
Route::view('/ultraschallgeraete/mobile-geraete/mindray-mu7', 'website.ultraschallgeraete.mobile-geraete.mindray-mu7')->name('website.ultraschallgeraete.mobile-geraete.mindray-mu7');
Route::view('/ultraschallgeraete/mobile-geraete/mindray-mx3', 'website.ultraschallgeraete.mobile-geraete.mindray-mx3')->name('website.ultraschallgeraete.mobile-geraete.mindray-mx3');
Route::view('/ultraschallgeraete/mobile-geraete/mindray-mx5', 'website.ultraschallgeraete.mobile-geraete.mindray-mx5')->name('website.ultraschallgeraete.mobile-geraete.mindray-mx5');
Route::view('/ultraschallgeraete/mobile-geraete/mindray-mx7', 'website.ultraschallgeraete.mobile-geraete.mindray-mx7')->name('website.ultraschallgeraete.mobile-geraete.mindray-mx7');
Route::view('/ultraschallgeraete/mobile-geraete/mindray-te-5', 'website.ultraschallgeraete.mobile-geraete.mindray-te-5')->name('website.ultraschallgeraete.mobile-geraete.mindray-te-5');
Route::view('/ultraschallgeraete/mobile-geraete/mindray-te-7-ace', 'website.ultraschallgeraete.mobile-geraete.mindray-te-7-ace')->name('website.ultraschallgeraete.mobile-geraete.mindray-te-7-ace');
Route::view('/ultraschallgeraete/mobile-geraete/mindray-te-9', 'website.ultraschallgeraete.mobile-geraete.mindray-te-9')->name('website.ultraschallgeraete.mobile-geraete.mindray-te-9');
Route::view('/ultraschallgeraete/mobile-geraete/mindray-z50', 'website.ultraschallgeraete.mobile-geraete.mindray-z50')->name('website.ultraschallgeraete.mobile-geraete.mindray-z50');
Route::view('/ultraschallgeraete/mobile-geraete/mindray-z60', 'website.ultraschallgeraete.mobile-geraete.mindray-z60')->name('website.ultraschallgeraete.mobile-geraete.mindray-z60');
Route::view('/ultraschallgeraete/sono-finder', 'website.ultraschallgeraete.sono-finder')->name('website.ultraschallgeraete.sono-finder');
Route::view('/ultraschallgeraete/standgeraete', 'website.ultraschallgeraete.standgeraete.index')->name('website.ultraschallgeraete.standgeraete.index');
Route::view('/ultraschallgeraete/standgeraete/esaote-mylab-a50', 'website.ultraschallgeraete.standgeraete.esaote-mylab-a50')->name('website.ultraschallgeraete.standgeraete.esaote-mylab-a50');
Route::view('/ultraschallgeraete/standgeraete/esaote-mylab-a70', 'website.ultraschallgeraete.standgeraete.esaote-mylab-a70')->name('website.ultraschallgeraete.standgeraete.esaote-mylab-a70');
Route::view('/ultraschallgeraete/standgeraete/mindray-consona-n5', 'website.ultraschallgeraete.standgeraete.mindray-consona-n5')->name('website.ultraschallgeraete.standgeraete.mindray-consona-n5');
Route::view('/ultraschallgeraete/standgeraete/mindray-consona-n6', 'website.ultraschallgeraete.standgeraete.mindray-consona-n6')->name('website.ultraschallgeraete.standgeraete.mindray-consona-n6');
Route::view('/ultraschallgeraete/standgeraete/mindray-consona-n8', 'website.ultraschallgeraete.standgeraete.mindray-consona-n8')->name('website.ultraschallgeraete.standgeraete.mindray-consona-n8');
Route::view('/ultraschallgeraete/standgeraete/mindray-consona-n9', 'website.ultraschallgeraete.standgeraete.mindray-consona-n9')->name('website.ultraschallgeraete.standgeraete.mindray-consona-n9');
Route::view('/ultraschallgeraete/standgeraete/mindray-dc-30', 'website.ultraschallgeraete.standgeraete.mindray-dc-30')->name('website.ultraschallgeraete.standgeraete.mindray-dc-30');
Route::view('/ultraschallgeraete/standgeraete/mindray-dc-60', 'website.ultraschallgeraete.standgeraete.mindray-dc-60')->name('website.ultraschallgeraete.standgeraete.mindray-dc-60');
Route::view('/ultraschallgeraete/standgeraete/mindray-nuewa-i10', 'website.ultraschallgeraete.standgeraete.mindray-nuewa-i10')->name('website.ultraschallgeraete.standgeraete.mindray-nuewa-i10');
Route::view('/ultraschallgeraete/standgeraete/mindray-nuewa-i9', 'website.ultraschallgeraete.standgeraete.mindray-nuewa-i9')->name('website.ultraschallgeraete.standgeraete.mindray-nuewa-i9');
Route::view('/ultraschallgeraete/standgeraete/mindray-resona-i8', 'website.ultraschallgeraete.standgeraete.mindray-resona-i8')->name('website.ultraschallgeraete.standgeraete.mindray-resona-i8');
Route::view('/ultraschallgeraete/standgeraete/mindray-resona-i9', 'website.ultraschallgeraete.standgeraete.mindray-resona-i9')->name('website.ultraschallgeraete.standgeraete.mindray-resona-i9');
Route::view('/veranstaltungen', 'website.veranstaltungen')->name('website.veranstaltungen');

/*
 * Nur lokal und im Test: zeigt, welcher Zugriffspunkt und welche Datenbankrolle
 * den Request bedient haben. Traegt die Architektur-Beweise aus
 * tests/Architecture/, damit die nicht vom Seiteninhalt abhaengen (ADR-036).
 */
if (app()->environment(['local', 'testing'])) {
    Route::get('/__access-point', fn () => AccessPoint::describe('website', 'Website', 'Blade'))
        ->name('website.access-point');
}
