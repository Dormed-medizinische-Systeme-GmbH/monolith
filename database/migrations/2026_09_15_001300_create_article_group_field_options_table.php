<?php

declare(strict_types=1);

use App\Support\Columns;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Die Werteliste eines Feldes vom Typ `select` (D-134).
 *
 * Ersetzt ein frueheres `options`-JSON. Werte REFERENZIEREN die Option, sie
 * kopieren sie nicht: wird eine Option umbenannt, aendert sich die Bezeichnung
 * ueberall mit, statt in kopierten Zeichenketten zurueckzubleiben.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('article_group_field_options', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('field_id')->constrained('article_group_fields')->cascadeOnDelete();

            $table->string('value');
            $table->string('label');
            $table->smallInteger('position')->nullable();
            $table->boolean('is_active')->default(true);

            Columns::blame($table);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['field_id', 'value']);
            $table->index(['field_id', 'position']);
        });

        /*
         * Anker fuer den zusammengesetzten Fremdschluessel der Wertetabelle.
         * Ohne ihn liesse sich einem Feld eine Option eines ANDEREN Feldes
         * zuweisen — der einfache FK auf `id` allein prueft das nicht.
         */
        DB::statement('ALTER TABLE article_group_field_options
            ADD CONSTRAINT article_group_field_options_id_field_unique UNIQUE (id, field_id)');
    }

    public function down(): void
    {
        Schema::dropIfExists('article_group_field_options');
    }
};
