<?php

declare(strict_types=1);

use App\Support\Columns;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Die explizite Beziehung Person <-> Company (CORE.md).
 *
 * Eine Person kann Kontakt mehrerer Companies sein — genau das ist die offene
 * Frage 2 aus ADR-037: `app.company_id` ist dann nicht eindeutig.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_contacts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('person_id')->constrained('people')->cascadeOnDelete();

            // GENAU EINE Rolle (D-005). Vorschlagsliste im UI, Freitext
            // erlaubt, nicht auswertungsrelevant — deshalb kein Enum.
            $table->string('role')->nullable();

            $table->string('department')->nullable();
            $table->boolean('is_primary')->default(false);

            Columns::blame($table);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'person_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_contacts');
    }
};
