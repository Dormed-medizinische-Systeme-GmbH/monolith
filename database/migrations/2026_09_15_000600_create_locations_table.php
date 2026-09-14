<?php

declare(strict_types=1);

use App\Support\Columns;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Realer Betriebs-/Servicestandort einer Company (CORE.md, D-007).
 *
 * Jede Company hat mindestens eine Location; Device und ServiceContract
 * referenzieren spaeter die LOCATION, nicht die Company.
 *
 * Der Praxis-Netzwerk-Block (D-092) ersetzt 14 Legacy-Felder und ist
 * standort-, nicht geraetebezogen.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('locations', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('name');
            $table->text('notes')->nullable();

            // Genau eine je Company = Hauptstandort.
            $table->boolean('is_primary')->default(false);

            $table->string('practice_software')->nullable();
            $table->text('practice_it_notes')->nullable();
            $table->string('network_server_user')->nullable();
            $table->string('network_gateway')->nullable();
            $table->string('practice_hardware_asp')->nullable();
            $table->string('network_server_ip')->nullable();
            $table->string('practice_it_asp')->nullable();
            $table->string('network_storage_path')->nullable();

            // Verschluesselt im Model-Cast, nicht in der Spalte.
            $table->text('network_server_password')->nullable();

            $table->string('storage_ae_title')->nullable();
            $table->integer('storage_port')->nullable();
            $table->string('network_subnet_mask')->nullable();
            $table->integer('worklist_port')->nullable();
            $table->string('worklist_ae_title')->nullable();

            Columns::blame($table);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
