<?php

declare(strict_types=1);

use App\Support\Columns;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Die juristische/organisatorische Kunden-Einheit (CORE.md).
 *
 * Aktuell ausschliesslich Kunden — Lieferanten sind ein spaeterer Bereich
 * (D-008). Companies sind flach, keine Hierarchie ausser der
 * Rechnungsempfaenger-Beziehung (D-006).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('name_addition')->nullable();
            $table->foreignId('medical_specialty_id')->nullable()->constrained('medical_specialties')->nullOnDelete();
            $table->text('notes')->nullable();

            // Eigene interne Kundennummer, KEIN Fremdsystem-Bezug (D-009/D-073).
            $table->string('debitor_number')->nullable()->index();

            // Auftragsverarbeitungsvertrag (D-013).
            $table->string('avv_status')->default('none');
            $table->date('avv_signed_at')->nullable();

            // Informativ, KEINE Autorisierung (D-016).
            $table->foreignId('responsible_sales_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('responsible_service_id')->nullable()->constrained('users')->nullOnDelete();

            // Abweichende Rechnungsanschrift => andere Company (D-004/D-066).
            // Alle Rechnungen dieser Praxis gehen dorthin; keine
            // praxisuebergreifende Sammelrechnung.
            $table->foreignId('billing_company_id')->nullable()->constrained('companies')->nullOnDelete();

            // travel_zone_id fehlt bewusst: `travel_zones` wird im
            // Service-Bereich definiert (D-020/D-059/D-063) und existiert noch
            // nicht. Wird mit dem Service-Modul nachgezogen (ADR-010).

            $table->string('iban', 34)->nullable();
            $table->string('bic', 11)->nullable();
            $table->string('bank_account_holder')->nullable();
            $table->string('bank_name')->nullable();

            $table->string('legal_form')->nullable();
            $table->string('tax_number')->nullable();
            $table->string('vat_id')->nullable();
            $table->string('wid_number')->nullable();
            $table->string('trade_register_number')->nullable();
            $table->string('register_court')->nullable();

            Columns::blame($table);
            $table->timestamps();
            $table->softDeletes();

            $table->index('name');
        });

        Columns::check('companies', 'avv_status', ['none', 'signed']);
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
