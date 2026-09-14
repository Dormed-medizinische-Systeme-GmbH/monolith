<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Kundenzugaenge fuer Portal und Shop (ADR-037/042).
 *
 * Getrennt von `users`, und zwar strukturell: bei 1.600 Kundenkonten gegen 20
 * Mitarbeiterkonten in einer Tabelle waere das Einzige, was sie trennt, ein
 * `where`, das jemand vergessen kann.
 *
 * Der Zugang haengt fachlich am CRM-Kontakt (`person_id`), liegt aber NICHT als
 * Spalten auf `people`: von rund 50.000 Personen hat nur ein Bruchteil je einen
 * Zugang, und Auth-Belange — 2FA, Sperren, fehlgeschlagene Anmeldungen —
 * gehoeren nicht in eine Stammdatentabelle.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_accounts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('person_id')->unique()->constrained('people')->cascadeOnDelete();

            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();

            // Sperren statt loeschen: der Kontakt bleibt im CRM bestehen,
            // nur der Zugang ruht.
            $table->boolean('is_active')->default(true);

            $table->timestamp('last_login_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

        // Eigener Broker mit eigener Token-Tabelle (ADR-043) — in
        // config/auth.php nativ vorgesehen, kein Kunstgriff.
        Schema::create('customer_password_reset_tokens', function (Blueprint $table): void {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_password_reset_tokens');
        Schema::dropIfExists('customer_accounts');
    }
};
