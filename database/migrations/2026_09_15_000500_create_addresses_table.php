<?php

declare(strict_types=1);

use App\Support\Columns;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Polymorph an Company (Sitz) und Location — genau EINE Adresse je Besitzer
 * (CORE.md, D-003/D-014).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('addresses', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuidMorphs('addressable');

            $table->string('street');
            $table->string('house_number', 32)->nullable();
            $table->string('postal_code', 20);
            $table->string('city');
            $table->string('district')->nullable();
            $table->string('state')->nullable();
            $table->string('country_code', 2)->default('DE');

            $table->string('po_box')->nullable();
            $table->string('po_box_postal_code')->nullable();
            $table->string('po_box_city')->nullable();

            // Grundlage der Fahrtzone (D-024) — deshalb wichtig, nicht Beiwerk.
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('geocode_status')->default('pending');

            $table->dateTime('verified_at')->nullable();
            $table->string('verified_by')->nullable();

            Columns::blame($table);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['addressable_type', 'addressable_id']);
        });

        Columns::check('addresses', 'geocode_status', ['pending', 'ok', 'failed', 'manual']);
    }

    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
