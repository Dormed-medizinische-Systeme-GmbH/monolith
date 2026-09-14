<?php

declare(strict_types=1);

use App\Support\Columns;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Artikel — die Katalogstufe zwischen Gruppe und Exemplar (D-099).
 *
 * Die Feldliste ist bewusst breit angelegt (D-115) und soll AUS DER NUTZUNG
 * HERAUS gekuerzt werden, nicht aus einem Schema-Abgleich: fuer diesen Bereich
 * existiert keine Legacy-Vorlage (D-108). Jede Streichung wird als eigene
 * D-NNN vermerkt.
 *
 * Was hier NICHT steht: der Bestand. Er ist keine Spalte, sondern die Summe
 * ueber den Bewegungs-Ledger (D-102/D-093).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('articles', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            // Bestimmt den Feldkatalog (D-100). `restrictOnDelete`: eine Gruppe
            // mit Artikeln darf nicht verschwinden, sonst haetten die Artikel
            // kein Feldset mehr.
            $table->foreignUuid('article_group_id')->constrained('article_groups')->restrictOnDelete();

            // Gehoert zum ARTIKEL, nicht zum Exemplar (D-099). Die Seriennummer
            // sitzt am Exemplar.
            $table->string('article_number')->unique();

            $table->string('name');
            $table->text('description')->nullable();

            // Steuert, ob Exemplare (`devices`) existieren.
            $table->boolean('is_serial_tracked')->default(false);

            // Servicerelevant ⇒ erscheint im Artikel-Tab des Technikers (D-109).
            $table->boolean('is_service_item')->default(false);

            // Drei voneinander unabhaengige Schalter (ADR-038): Katalogpflege,
            // Website-Sichtbarkeit und Shop-Bestellbarkeit sind nicht dasselbe.
            // Nicht jeder Artikel im Warenwirtschaftsstamm gehoert auf die
            // Website, und nicht alles auf der Website ist bestellbar.
            $table->boolean('is_active')->default(true);
            $table->boolean('is_public')->default(false);
            $table->boolean('is_orderable')->default(false);

            $table->string('unit');
            $table->string('cost_center')->nullable();

            // Vorbelegung, massgeblich ist der Wert an der Position (D-074).
            $table->string('tax_category')->default('standard_19');
            $table->decimal('tax_rate', 5, 2)->default(19);

            // Listenpreise. KEINE Preislisten, keine Gueltigkeitszeitraeume,
            // keine Staffeln (D-104) — beim Einfuegen in eine Position wird der
            // Preis gesnapshottet, eine spaetere Aenderung wirkt nie rueckwirkend.
            $table->decimal('sale_price', 12, 2)->default(0);
            $table->decimal('purchase_price', 12, 2)->nullable();

            // Modelleigenschaften, vom Exemplar heruebergezogen (D-099).
            $table->string('manufacturer')->nullable();
            $table->string('model_name')->nullable();
            $table->string('manufacturer_article_number')->nullable();

            $table->string('ean')->nullable();
            $table->decimal('weight_kg', 10, 3)->nullable();

            // Ausloeser fuer Nachbestellung und Meldung.
            $table->decimal('min_stock', 10, 2)->nullable();

            $table->text('notes')->nullable();

            Columns::blame($table);
            $table->timestamps();
            $table->softDeletes();

            $table->index('name');
            $table->index(['is_active', 'is_public']);
            $table->index('is_service_item');
        });

        Columns::check('articles', 'tax_category', [
            'standard_19', 'reverse_charge', 'export_tax_free', 'other_tax_free',
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
