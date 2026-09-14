<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| dormed.de — der portierte Auftritt (ADR-038/039)
|--------------------------------------------------------------------------
|
| 78 Seiten aus dem Altprojekt. Der Wert dieses Tests liegt nicht im Inhalt,
| sondern darin, dass eine fehlende View oder eine kaputte Blade-Komponente
| beim naechsten Umbau sofort auffaellt statt erst im Live-Betrieb.
|
*/

test('jede Seite des Auftritts antwortet', function (): void {
    $paths = collect(Route::getRoutes())
        ->filter(fn ($route) => str_starts_with((string) $route->getName(), 'website.'))
        ->filter(fn ($route) => in_array('GET', $route->methods(), true))
        ->reject(fn ($route) => str_contains($route->uri(), '{'))
        ->reject(fn ($route) => in_array($route->getName(), ['website.access-point', 'website.sitemap', 'website.sitemap.system-pages'], true))
        ->map(fn ($route) => $route->uri() === '/' ? '/' : '/'.$route->uri())
        ->values();

    expect($paths)->toHaveCount(78);

    $broken = [];

    foreach ($paths as $path) {
        $response = $this->get('http://'.config('domains.website').$path);

        if ($response->getStatusCode() !== 200) {
            $broken[] = $path.' -> HTTP '.$response->getStatusCode();
        }
    }

    expect($broken)->toBe([], 'Defekte Seiten: '.implode(', ', $broken));
});

test('die Sitemap wird als XML ausgeliefert', function (): void {
    $this->get('http://'.config('domains.website').'/sitemap.xml')
        ->assertOk()
        ->assertHeader('Content-Type', 'application/xml')
        ->assertSee('<sitemapindex', false);
});

test('ein Produkt-Kurzprofil wird als Markdown ausgeliefert', function (): void {
    $this->get('http://'.config('domains.website').'/ultraschallgeraete/standgeraete/mindray-consona-n8.md')
        ->assertOk()
        ->assertHeader('Content-Type', 'text/markdown; charset=UTF-8');
});

test('ein Kurzprofil ohne Datei ergibt 404', function (): void {
    $this->get('http://'.config('domains.website').'/ultraschallgeraete/gibt-es-nicht.md')
        ->assertNotFound();
});
