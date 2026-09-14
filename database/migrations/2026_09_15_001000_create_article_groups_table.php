<?php

declare(strict_types=1);

use App\Support\Columns;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Artikelgruppe — hierarchisch, ABER ohne Feldvererbung (D-110).
 *
 * Der Baum dient ausschliesslich Navigation und Filterung. Das Feldset kommt
 * NUR aus der Gruppe, in der der Artikel tatsaechlich liegt; Obergruppen
 * vererben nichts. Bewusst in Kauf genommen: `Netzkabel` und `USB-Kabel`
 * brauchen beide ein Feld „Laenge" und bekommen es je einzeln. Gegenwert: das
 * effektive Feldset wird abgelesen statt ueber den Baum berechnet.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('article_groups', function (Blueprint $table): void {
            $table->uuid('id')->primary();

            // Der Fremdschluessel auf die eigene Tabelle folgt unten: bei einem
            // UUID-Primaerschluessel setzt Postgres den PRIMARY KEY erst per
            // ALTER TABLE nach den Fremdschluesseln (ADR-046).
            $table->uuid('parent_id')->nullable();

            $table->string('name');
            $table->text('description')->nullable();
            $table->smallInteger('position')->nullable();
            $table->boolean('is_active')->default(true);

            Columns::blame($table);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['parent_id', 'position']);
        });

        Schema::table('article_groups', function (Blueprint $table): void {
            // `nullOnDelete`, nicht `cascade`: eine Obergruppe zu loeschen darf
            // nicht stillschweigend den ganzen Ast mitnehmen.
            $table->foreign('parent_id')->references('id')->on('article_groups')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('article_groups');
    }
};
