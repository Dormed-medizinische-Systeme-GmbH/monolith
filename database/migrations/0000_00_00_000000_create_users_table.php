<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Mitarbeiter-Identitaet (IDENTITY_RBAC.md, D-124/D-125, ADR-042).
 *
 * `users` sind ausschliesslich MITARBEITER. Kundenzugaenge liegen in
 * `customer_accounts` — getrennte Tabelle, getrenntes Model, getrennter Guard.
 * Bei 1.600 Kundenkonten gegen 20 Mitarbeiterkonten in einer Tabelle waere das
 * Einzige, was sie trennt, ein `where`, das jemand vergessen kann.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('key')->unique();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();

            // Nullable: SSO-Nutzer haben spaeter kein Passwort (D-029).
            $table->string('password')->nullable();

            // Jetzt reserviert, von Entra-SSO befuellt (D-029). Der
            // Passwort-Login ist die Uebergangsloesung, nicht das Ziel.
            $table->string('entra_oid')->nullable()->unique();

            // Bootstrap-/IT-Bypass (D-028) — ersetzt die gestrichene Rolle `it`.
            $table->boolean('is_admin')->default(false);

            // Inaktiv => kein Login, aus `responsible_*` ausgeblendet.
            $table->boolean('is_active')->default(true);

            // NOT NULL (D-124): genau eine Rolle je Mitarbeiter. Der Pivot
            // `role_user` und `is_primary` sind ersatzlos entfallen.
            $table->foreignUuid('role_id')->constrained('roles')->restrictOnDelete();

            /*
             * Der Ablagepfad des Mitarbeiterfotos im Object Storage (ADR-045),
             * z. B. `employees/A.Draheim.jpg`. Nur der Schluessel, nie eine
             * vollstaendige Adresse — die baut `Storage::url()` aus `AWS_URL`,
             * und ein Wechsel des Hostnamens bliebe sonst in jeder Zeile stehen.
             *
             * Kein Widerspruch zu D-033: gestrichen sind dort Personalnummer,
             * Kostenstelle und Ein-/Austrittsdatum, also HR-Verwaltung. Das Foto
             * dient dem Wiedererkennen in Listen und Zuordnungen und ist damit
             * Bedienung, keine Personalakte.
             */
            $table->string('photo_path')->nullable();

            /*
             * Der Dormed-Standort, an dem dieser Mitarbeiter sitzt. Der
             * Fremdschluessel folgt in `create_sites_table` — diese Migration
             * laeuft zuerst, `sites` gibt es hier noch nicht.
             *
             * Nullable: wer im Aussendienst sitzt oder neu ist, hat noch keinen.
             */
            $table->uuid('site_id')->nullable()->index();

            $table->timestamp('last_login_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table): void {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table): void {
            $table->string('id')->primary();
            $table->foreignUuid('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
        Schema::dropIfExists('roles');
    }
};
