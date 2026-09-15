<?php

declare(strict_types=1);

use App\Modules\Core\Models\Employee;
use App\Modules\Inventory\Enums\FieldScope;
use App\Modules\Inventory\Enums\FieldType;
use App\Modules\Inventory\Models\Article;
use App\Modules\Inventory\Models\ArticleGroup;
use App\Modules\Inventory\Models\ArticleGroupField;
use App\Modules\Inventory\Services\ArticleFieldValues;
use Database\Seeders\RoleSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function (): void {
    (new RoleSeeder)->run();

    $this->ober = ArticleGroup::query()->create(['name' => 'Ultraschall']);
    $this->gruppe = ArticleGroup::query()->create([
        'name' => 'Standgeräte',
        'parent_id' => $this->ober->id,
    ]);

    $this->artikel = Article::query()->create([
        'article_group_id' => $this->gruppe->id,
        'article_number' => 'US-1001',
        'name' => 'Mindray Resona i9',
        'unit' => 'Stk',
        'sale_price' => 48900,
        'is_serial_tracked' => true,
    ]);
});

function besucheArtikelDetail(object $test, Article $artikel): object
{
    return $test->actingAs(Employee::factory()->create(), 'staff')
        ->get('http://'.config('domains.erp').'/artikel/'.$artikel->id);
}

test('die Detailansicht verlangt eine Anmeldung', function (): void {
    $this->get('http://'.config('domains.erp').'/artikel/'.$this->artikel->id)
        ->assertRedirect('http://'.config('domains.erp').'/login');
});

test('sie zeigt Nummer, Gruppenpfad und Preise', function (): void {
    besucheArtikelDetail($this, $this->artikel)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('erp/articles/Show')
            ->where('article.articleNumber', 'US-1001')
            ->where('article.group.path', ['Ultraschall', 'Standgeräte'])
            ->where('article.preise.Verkauf', '48.900,00 €')
            ->where('article.flags.serialTracked', true)
        );
});

test('die Merkmale kommen aus der Gruppe, die Werte vom Artikel', function (): void {
    $feld = ArticleGroupField::query()->create([
        'article_group_id' => $this->gruppe->id,
        'key' => 'plattform',
        'label' => 'Plattform',
        'type' => FieldType::String,
        'scope' => FieldScope::Article,
    ]);

    ArticleFieldValues::set($this->artikel, $feld, 'ZST+');

    besucheArtikelDetail($this, $this->artikel)
        ->assertInertia(fn (Assert $page) => $page
            ->has('article.merkmale', 1)
            ->where('article.merkmale.0.label', 'Plattform')
            ->where('article.merkmale.0.value', 'ZST+')
        );
});

test('ein Pflichtfeld ohne Wert steht sichtbar leer da', function (): void {
    /*
     * Es fehlt nicht einfach: die Ansicht zaehlt die FELDER der Gruppe auf, nicht
     * die vorhandenen Werte. Sonst waere eine Luecke im Stamm unsichtbar.
     */
    ArticleGroupField::query()->create([
        'article_group_id' => $this->gruppe->id,
        'key' => 'monitor',
        'label' => 'Monitor',
        'type' => FieldType::String,
        'scope' => FieldScope::Article,
        'is_mandatory' => true,
    ]);

    besucheArtikelDetail($this, $this->artikel)
        ->assertInertia(fn (Assert $page) => $page
            ->has('article.merkmale', 1)
            ->where('article.merkmale.0.mandatory', true)
            ->where('article.merkmale.0.value', null)
        );
});

test('ein select-Merkmal zeigt die Beschriftung der Option', function (): void {
    $feld = ArticleGroupField::query()->create([
        'article_group_id' => $this->gruppe->id,
        'key' => 'klasse',
        'label' => 'Klasse',
        'type' => FieldType::Select,
        'scope' => FieldScope::Article,
    ]);
    $option = $feld->options()->create(['value' => 'premium', 'label' => 'Premium']);

    ArticleFieldValues::set($this->artikel, $feld, $option);

    besucheArtikelDetail($this, $this->artikel)
        ->assertInertia(fn (Assert $page) => $page->where('article.merkmale.0.value', 'Premium'));
});

test('Felder je Exemplar erscheinen nicht am Artikel', function (): void {
    // `scope = item` haengt am Exemplar (D-119) und wird beim Wareneingang
    // erfasst — am Artikel waere die Zeile sinnlos.
    ArticleGroupField::query()->create([
        'article_group_id' => $this->gruppe->id,
        'key' => 'mac',
        'label' => 'MAC-Adresse',
        'type' => FieldType::String,
        'scope' => FieldScope::Item,
    ]);

    besucheArtikelDetail($this, $this->artikel)
        ->assertInertia(fn (Assert $page) => $page->has('article.merkmale', 0));
});

test('ein unsinniger Schluessel wird zu 404', function (): void {
    $this->actingAs(Employee::factory()->create(), 'staff')
        ->get('http://'.config('domains.erp').'/artikel/keine-uuid')
        ->assertNotFound();
});
