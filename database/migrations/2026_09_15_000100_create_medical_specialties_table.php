<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fachrichtungen (CORE.md, D-019/D-133).
 *
 * EINE Liste fuer alles: das Website-Kontaktformular zieht seine Auswahl aus
 * dieser Tabelle statt aus einem hartcodierten Array. Vorher waren es zwei
 * konkurrierende Listen.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('medical_specialties', function (Blueprint $table): void {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('medical_specialties');
    }
};
