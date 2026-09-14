<?php

declare(strict_types=1);

use App\Support\Columns;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Der benutzerdefinierte Feldkatalog einer Artikelgruppe (D-100/D-111/D-119).
 *
 * **Bewusste Ausnahme zu D-094:** die Werteliste dieser Felder ist im UI
 * pflegbar und kann per Definition keine Migration je Aenderung haben. Die
 * Ausnahme ist eng begrenzt — `type` und `scope` selbst sind feste Enums und
 * tragen ihren CHECK wie alles andere.
 *
 * Fuer Inventory ist dieser Katalog kein Komfort, sondern das Sicherheitsnetz:
 * es gibt keine Legacy-Vorlage (D-108), die feste Feldliste ist also geraten.
 * Was darin fehlt, laesst sich hierueber ohne Migration nachtragen.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('article_group_fields', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('article_group_id')->constrained('article_groups')->cascadeOnDelete();

            $table->string('key');
            $table->string('label');
            $table->string('type');
            $table->string('scope');

            $table->boolean('is_mandatory')->default(false);

            /*
             * Optionales Muster (D-111). Die Durchsetzung liegt vorerst in der
             * Anwendung: ein Postgres-CHECK kann das Muster nicht aus dieser
             * Tabelle lesen — CHECK-Ausdruecke duerfen keine Unterabfragen
             * enthalten. Fuer die Durchsetzung in der Datenbank braeuchte es
             * einen Trigger; das ist eine eigene Entscheidung und steht noch aus.
             */
            $table->string('validation_regex')->nullable();

            $table->smallInteger('position')->nullable();

            Columns::blame($table);
            $table->timestamps();
            $table->softDeletes();

            // Ein Schluessel je Gruppe.
            $table->unique(['article_group_id', 'key']);
            $table->index(['article_group_id', 'position']);
        });

        Columns::check('article_group_fields', 'type', [
            'string', 'text', 'integer', 'decimal', 'date', 'boolean', 'select',
        ]);
        Columns::check('article_group_fields', 'scope', ['article', 'item']);

        /*
         * Anker fuer den zusammengesetzten Fremdschluessel der Wertetabellen
         * (D-134). Diese beiden Spalten fuehren die Wertetabellen MIT und binden
         * sie hierueber — dadurch kann die mitgefuehrte Kopie konstruktions-
         * bedingt nicht von der Definition abweichen. Ohne diesen UNIQUE gaebe
         * es keinen Kandidatenschluessel, auf den der FK zeigen koennte.
         */
        DB::statement('ALTER TABLE article_group_fields
            ADD CONSTRAINT article_group_fields_id_type_scope_unique UNIQUE (id, type, scope)');
    }

    public function down(): void
    {
        Schema::dropIfExists('article_group_fields');
    }
};
