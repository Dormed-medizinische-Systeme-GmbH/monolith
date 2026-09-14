<?php

declare(strict_types=1);

use App\Support\Columns;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Die Werte der benutzerdefinierten Felder mit `scope = article` (D-134).
 *
 * **Typisierte Union statt EAV.** Je Zeile sieben Wertespalten, von denen genau
 * eine gesetzt ist — und zwar die, die zum `type` gehoert. Eine einzelne
 * VARCHAR-`value`-Spalte waere der klassische EAV-Fehler: sie gibt genau die
 * Typisierung auf, die D-094 und ADR-007 einfordern. Sechs leere Spalten sind
 * keine Redundanz und keine transitive Abhaengigkeit, also keine
 * Normalisierungsverletzung — dieselbe Unterscheidung wie bei den Snapshots in
 * D-093.
 *
 * Wie Postgres den richtigen Typ erzwingt: „genau eine Spalte gesetzt" ist
 * in-row pruefbar, „und zwar die passende zum `type`" nicht — der `type` steht
 * in der Definitionstabelle. Diese Tabelle fuehrt ihn deshalb MIT und bindet
 * ihn ueber einen zusammengesetzten Fremdschluessel zurueck. Die Kopie kann
 * damit konstruktionsbedingt nicht divergieren.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('article_field_values', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('article_id')->constrained('articles')->cascadeOnDelete();

            // Ohne `constrained()`: der Bezug laeuft ueber den zusammengesetzten
            // Fremdschluessel unten, ein zweiter auf `id` allein waere doppelt.
            $table->uuid('field_id');

            // Mitgefuehrte Kopie aus der Definition, siehe Klassenkommentar.
            $table->string('type');
            $table->string('scope');

            $table->string('value_string')->nullable();
            $table->text('value_text')->nullable();
            $table->bigInteger('value_integer')->nullable();
            $table->decimal('value_decimal', 15, 4)->nullable();
            $table->date('value_date')->nullable();
            $table->boolean('value_boolean')->nullable();
            $table->uuid('value_option_id')->nullable();

            Columns::blame($table);
            $table->timestamps();
            $table->softDeletes();

            // Ein Wert je Artikel und Feld.
            $table->unique(['article_id', 'field_id']);
        });

        /*
         * Typ UND Stufe kommen aus der Definition und koennen nicht abweichen.
         */
        DB::statement('ALTER TABLE article_field_values
            ADD CONSTRAINT article_field_values_field_foreign
            FOREIGN KEY (field_id, type, scope)
            REFERENCES article_group_fields (id, type, scope) ON DELETE CASCADE');

        /*
         * Diese Tabelle traegt ausschliesslich Werte der Stufe `article`. Werte
         * je Exemplar liegen in `device_field_values` — getrennte Tabellen,
         * damit jede einen ECHTEN Fremdschluessel hat statt einer polymorphen
         * Beziehung (D-134).
         */
        DB::statement("ALTER TABLE article_field_values
            ADD CONSTRAINT article_field_values_scope_check CHECK (scope = 'article')");

        /*
         * Die gewaehlte Option muss zu DIESEM Feld gehoeren. Ein FK auf `id`
         * allein liesse die Option eines fremden Feldes zu.
         */
        DB::statement('ALTER TABLE article_field_values
            ADD CONSTRAINT article_field_values_option_foreign
            FOREIGN KEY (value_option_id, field_id)
            REFERENCES article_group_field_options (id, field_id) ON DELETE RESTRICT');

        // Genau eine Wertespalte ist gesetzt.
        DB::statement('ALTER TABLE article_field_values
            ADD CONSTRAINT article_field_values_one_value_check CHECK (
                (value_string   IS NOT NULL)::int
              + (value_text     IS NOT NULL)::int
              + (value_integer  IS NOT NULL)::int
              + (value_decimal  IS NOT NULL)::int
              + (value_date     IS NOT NULL)::int
              + (value_boolean  IS NOT NULL)::int
              + (value_option_id IS NOT NULL)::int = 1
            )');

        // Und zwar die, die zum Typ gehoert.
        DB::statement("ALTER TABLE article_field_values
            ADD CONSTRAINT article_field_values_type_matches_value_check CHECK (
                   (type = 'string'  AND value_string    IS NOT NULL)
                OR (type = 'text'    AND value_text      IS NOT NULL)
                OR (type = 'integer' AND value_integer   IS NOT NULL)
                OR (type = 'decimal' AND value_decimal   IS NOT NULL)
                OR (type = 'date'    AND value_date      IS NOT NULL)
                OR (type = 'boolean' AND value_boolean   IS NOT NULL)
                OR (type = 'select'  AND value_option_id IS NOT NULL)
            )");
    }

    public function down(): void
    {
        Schema::dropIfExists('article_field_values');
    }
};
