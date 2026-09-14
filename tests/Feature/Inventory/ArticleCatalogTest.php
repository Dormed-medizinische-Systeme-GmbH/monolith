<?php

declare(strict_types=1);

use App\Modules\Inventory\Enums\FieldScope;
use App\Modules\Inventory\Enums\FieldType;
use App\Modules\Inventory\Enums\TaxCategory;
use App\Modules\Inventory\Models\Article;
use App\Modules\Inventory\Models\ArticleFieldValue;
use App\Modules\Inventory\Models\ArticleGroup;
use App\Modules\Inventory\Models\ArticleGroupField;
use App\Modules\Inventory\Services\ArticleFieldValues;
use Illuminate\Database\QueryException;

beforeEach(function (): void {
    $this->gruppe = ArticleGroup::query()->create(['name' => 'Standgeräte']);

    $this->artikel = Article::query()->create([
        'article_group_id' => $this->gruppe->id,
        'article_number' => 'A-1000',
        'name' => 'Mindray Resona i9',
        'unit' => 'Stk',
        'sale_price' => 49000,
        'is_serial_tracked' => true,
    ]);
});

function feld(ArticleGroup $gruppe, FieldType $type, array $attribute = []): ArticleGroupField
{
    return ArticleGroupField::query()->create([
        'article_group_id' => $gruppe->id,
        'key' => $attribute['key'] ?? 'feld_'.$type->value,
        'label' => $attribute['label'] ?? ucfirst($type->value),
        'type' => $type,
        'scope' => $attribute['scope'] ?? FieldScope::Article,
        ...$attribute,
    ]);
}

test('der Baum vererbt keine Felder', function (): void {
    /*
     * D-110: der Baum dient NUR Navigation. Das effektive Feldset kommt aus der
     * Gruppe, in der der Artikel liegt — nicht aus ihren Obergruppen. Wer hier
     * eine Vererbung einbaut, dreht die Entscheidung um.
     */
    $ober = ArticleGroup::query()->create(['name' => 'Ultraschall']);
    $this->gruppe->update(['parent_id' => $ober->id]);

    feld($ober, FieldType::String, ['key' => 'plattform', 'label' => 'Plattform']);

    expect($this->gruppe->fields()->count())->toBe(0);
    expect($this->gruppe->parent->fields()->count())->toBe(1);
});

test('ein Wert landet in der Spalte seines Typs', function (FieldType $type, mixed $eingabe, mixed $erwartet): void {
    $wert = ArticleFieldValues::set($this->artikel, feld($this->gruppe, $type), $eingabe);

    expect($wert->getAttribute($type->column()))->not->toBeNull();
    expect($wert->value())->toEqual($erwartet);
})->with([
    [FieldType::String, 'ZST+', 'ZST+'],
    [FieldType::Text, "Zeile 1\nZeile 2", "Zeile 1\nZeile 2"],
    [FieldType::Integer, 4, 4],
    [FieldType::Boolean, true, true],
]);

test('die Datenbank laesst nur die passende Wertespalte zu', function (): void {
    /*
     * Der Kern von D-134: „genau eine Spalte gesetzt" ist in-row pruefbar,
     * „und zwar die passende zum Typ" nur, weil die Zeile den Typ mitfuehrt.
     * Ohne diesen CHECK waere die Konstruktion ein gewoehnliches EAV.
     */
    $feld = feld($this->gruppe, FieldType::Integer);

    expect(fn () => ArticleFieldValue::query()->create([
        'article_id' => $this->artikel->id,
        'field_id' => $feld->id,
        'type' => FieldType::Integer,
        'scope' => FieldScope::Article,
        'value_string' => 'vier',
    ]))->toThrow(QueryException::class);
});

test('die Datenbank laesst nicht zwei Wertespalten gleichzeitig zu', function (): void {
    $feld = feld($this->gruppe, FieldType::String);

    expect(fn () => ArticleFieldValue::query()->create([
        'article_id' => $this->artikel->id,
        'field_id' => $feld->id,
        'type' => FieldType::String,
        'scope' => FieldScope::Article,
        'value_string' => 'a',
        'value_integer' => 1,
    ]))->toThrow(QueryException::class);
});

test('ein mitgefuehrter Typ kann nicht von der Definition abweichen', function (): void {
    // Der zusammengesetzte Fremdschluessel (field_id, type, scope) findet keine
    // Definitionszeile — die Kopie kann konstruktionsbedingt nicht divergieren.
    $feld = feld($this->gruppe, FieldType::String);

    expect(fn () => ArticleFieldValue::query()->create([
        'article_id' => $this->artikel->id,
        'field_id' => $feld->id,
        'type' => FieldType::Integer,
        'scope' => FieldScope::Article,
        'value_integer' => 1,
    ]))->toThrow(QueryException::class);
});

test('ein select-Wert referenziert die Option, statt ihren Text zu kopieren', function (): void {
    $feld = feld($this->gruppe, FieldType::Select, ['key' => 'klasse', 'label' => 'Klasse']);
    $option = $feld->options()->create(['value' => 'premium', 'label' => 'Premium']);

    $wert = ArticleFieldValues::set($this->artikel, $feld, $option);

    expect($wert->value_option_id)->toBe($option->id);

    // Eine Umbenennung wirkt ueberall, statt in kopierten Zeichenketten zurueckzubleiben.
    $option->update(['label' => 'Oberklasse']);

    expect($wert->fresh()->option->label)->toBe('Oberklasse');
});

test('eine Option eines fremden Feldes wird abgewiesen', function (): void {
    $feld = feld($this->gruppe, FieldType::Select, ['key' => 'klasse']);
    $fremd = feld($this->gruppe, FieldType::Select, ['key' => 'bauform']);
    $fremdeOption = $fremd->options()->create(['value' => 'mobil', 'label' => 'Mobil']);

    expect(fn () => ArticleFieldValue::query()->create([
        'article_id' => $this->artikel->id,
        'field_id' => $feld->id,
        'type' => FieldType::Select,
        'scope' => FieldScope::Article,
        'value_option_id' => $fremdeOption->id,
    ]))->toThrow(QueryException::class);
});

test('ein Feld einer anderen Gruppe wird abgewiesen', function (): void {
    $andere = ArticleGroup::query()->create(['name' => 'Kabel']);

    expect(fn () => ArticleFieldValues::set($this->artikel, feld($andere, FieldType::String), 'x'))
        ->toThrow(InvalidArgumentException::class);
});

test('ein Feld je Exemplar gehoert nicht an den Artikel', function (): void {
    // `scope = item` haengt am Exemplar (D-119) und landet in
    // `device_field_values` — der CHECK dieser Tabelle laesst nur `article` zu.
    $feld = feld($this->gruppe, FieldType::String, ['scope' => FieldScope::Item, 'key' => 'mac']);

    expect(fn () => ArticleFieldValues::set($this->artikel, $feld, 'AA:BB'))
        ->toThrow(InvalidArgumentException::class);
});

test('das hinterlegte Muster wird durchgesetzt', function (): void {
    $feld = feld($this->gruppe, FieldType::String, [
        'key' => 'ean',
        'validation_regex' => '^\d{13}$',
    ]);

    expect(fn () => ArticleFieldValues::set($this->artikel, $feld, 'keine-ean'))
        ->toThrow(InvalidArgumentException::class);

    expect(ArticleFieldValues::set($this->artikel, $feld, '4006381333931')->value())
        ->toBe('4006381333931');
});

test('ein Wert wird ersetzt und nicht verdoppelt', function (): void {
    $feld = feld($this->gruppe, FieldType::String);

    ArticleFieldValues::set($this->artikel, $feld, 'alt');
    ArticleFieldValues::set($this->artikel, $feld, 'neu');

    expect($this->artikel->fieldValues()->count())->toBe(1);
    expect($this->artikel->fieldValues()->first()->value())->toBe('neu');
});

test('die Steuerkategorie ist am Artikel nur Vorbelegung und faellt auf den Regelsatz', function (): void {
    expect($this->artikel->tax_category)->toBe(TaxCategory::Standard19);
    expect((float) $this->artikel->tax_rate)->toBe(19.0);
});

test('Katalogpflege, Website und Shop sind drei unabhaengige Schalter', function (): void {
    /*
     * ADR-038: nicht jeder Artikel im Warenwirtschaftsstamm gehoert auf die
     * Website, und nicht alles auf der Website ist bestellbar.
     */
    expect($this->artikel->is_active)->toBeTrue();
    expect($this->artikel->is_public)->toBeFalse();
    expect($this->artikel->is_orderable)->toBeFalse();
});

test('die Artikelnummer ist eindeutig', function (): void {
    expect(fn () => Article::query()->create([
        'article_group_id' => $this->gruppe->id,
        'article_number' => 'A-1000',
        'name' => 'Dublette',
        'unit' => 'Stk',
    ]))->toThrow(QueryException::class);
});
