<?php

declare(strict_types=1);

use App\Modules\Core\Models\User;
use App\Modules\Inventory\Models\Article;
use App\Modules\Inventory\Models\ArticleGroup;
use Database\Seeders\RoleSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function (): void {
    (new RoleSeeder)->run();

    $this->gruppe = ArticleGroup::query()->create(['name' => 'Standgeräte']);
});

function artikel(ArticleGroup $gruppe, string $nummer, string $name, array $attribute = []): Article
{
    return Article::query()->create([
        'article_group_id' => $gruppe->id,
        'article_number' => $nummer,
        'name' => $name,
        'unit' => 'Stk',
        ...$attribute,
    ]);
}

function besucheArtikel(object $test, string $query = ''): object
{
    return $test->actingAs(User::factory()->create(), 'staff')
        ->get('http://'.config('domains.erp').'/artikel'.$query);
}

test('die Liste verlangt eine Anmeldung', function (): void {
    $this->get('http://'.config('domains.erp').'/artikel')
        ->assertRedirect('http://'.config('domains.erp').'/login');
});

test('sie zeigt Artikel, Nummer, Gruppe und Preis', function (): void {
    artikel($this->gruppe, 'US-1001', 'Mindray Resona i9', [
        'manufacturer' => 'Mindray',
        'model_name' => 'Resona i9',
        'sale_price' => 48900,
    ]);

    besucheArtikel($this)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('erp/articles/Index')
            ->where('rows.0.name', 'Mindray Resona i9')
            ->where('rows.0.articleNumber', 'US-1001')
            ->where('rows.0.group', 'Standgeräte')
            ->where('rows.0.manufacturer', 'Mindray Resona i9')
            // Formatiert auf dem Server — im Browser wuerde aus decimal(12,2)
            // eine Gleitkommazahl.
            ->where('rows.0.price', '48.900,00 €')
            ->where('rows.0.unit', 'Stk')
        );
});

test('die Suche greift auf Name, Nummer, Hersteller und EAN', function (string $suche, string $erwartet): void {
    artikel($this->gruppe, 'US-1001', 'Resona i9', ['manufacturer' => 'Mindray', 'ean' => '4006381333931']);
    artikel($this->gruppe, 'ZB-3001', 'Netzkabel C13', ['manufacturer' => 'Bachmann']);

    besucheArtikel($this, '?suche='.urlencode($suche))
        ->assertInertia(fn (Assert $page) => $page
            ->where('meta.total', 1)
            ->where('rows.0.name', $erwartet)
        );
})->with([
    ['Resona', 'Resona i9'],
    ['ZB-3001', 'Netzkabel C13'],
    ['Bachmann', 'Netzkabel C13'],
    ['4006381333931', 'Resona i9'],
]);

test('nach dem Preis laesst sich sortieren', function (): void {
    artikel($this->gruppe, 'A-1', 'Teuer', ['sale_price' => 1000]);
    artikel($this->gruppe, 'A-2', 'Günstig', ['sale_price' => 10]);

    besucheArtikel($this, '?sortierung=price')
        ->assertInertia(fn (Assert $page) => $page
            ->where('meta.sort', 'price')
            ->where('rows.0.name', 'Günstig')
        );
});

test('sortiert wird nur nach freigegebenen Spalten', function (): void {
    artikel($this->gruppe, 'A-1', 'Irgendwas');

    besucheArtikel($this, '?sortierung=articles.purchase_price')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('meta.sort', 'name'));
});

test('die drei Sichtbarkeitsschalter kommen einzeln durch', function (): void {
    artikel($this->gruppe, 'A-1', 'Nur Website', ['is_public' => true]);

    besucheArtikel($this)
        ->assertInertia(fn (Assert $page) => $page
            ->where('rows.0.isActive', true)
            ->where('rows.0.isPublic', true)
            ->where('rows.0.isOrderable', false)
        );
});
