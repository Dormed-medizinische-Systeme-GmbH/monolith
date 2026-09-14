<?php

declare(strict_types=1);

use App\Support\Columns;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Eine reale Person (CORE.md).
 *
 * Nie eigenstaendig — Anlage immer im Kontext einer Company (D-002). Eine
 * Person hat KEINE eigene Adresse (D-014); die Briefanrede wird bei der
 * Dokumenterstellung erzeugt, nicht gespeichert (D-015).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('people', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('first_name');
            $table->string('last_name');

            // Namenszusatz, z. B. „Dr. med." (D-025). Kann spaeter mit `title`
            // verschmelzen.
            $table->string('name_suffix')->nullable();
            $table->string('title')->nullable();

            $table->string('gender');

            // Aktuell nur DE-Kunden (D-025).
            $table->string('locale', 5)->default('de');

            Columns::blame($table);
            $table->timestamps();
            $table->softDeletes();

            $table->index('last_name');
        });

        Columns::check('people', 'gender', ['maennlich', 'weiblich', 'divers', 'unbekannt']);
    }

    public function down(): void
    {
        Schema::dropIfExists('people');
    }
};
