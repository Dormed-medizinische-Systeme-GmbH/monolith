<?php

declare(strict_types=1);

use App\Support\Columns;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Die eigenen Standorte von Dormed — Buchholz, Stuttgart.
 *
 * **Bewusst NICHT `locations`.** Die sind Kundenstandorte: `company_id` ist
 * dort `NOT NULL`, und `companies` fuehrt ausschliesslich Kunden (D-002/D-007).
 * Einen Mitarbeiter dorthin zu haengen hiesse, ihn einer Kundenpraxis
 * zuzuordnen. `company_id` nullable zu machen waere die Alternative gewesen —
 * dann braeuchte aber jede bestehende und kuenftige Abfrage auf `locations` ein
 * `whereNotNull('company_id')`, das jemand vergisst.
 *
 * **Eigene Adressspalten statt der polymorphen `addresses`.** Die liegt in
 * `Modules\Crm`, und `Core` haengt von NICHTS ab (`module.php`) — ein Zugriff
 * darauf machte den Modulgraph zyklisch (Crm → Core → Crm), was ADR-005
 * verbietet. Fachlich passt es ohnehin: die Kundenadresse traegt Geokodierung
 * und Pruefvermerk, weil daran die Fahrtzone haengt (D-024). Ein eigener
 * Standort braucht davon nichts.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sites', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('name');

            /*
             * Strasse und Hausnummer in EINEM Feld — anders als bei den
             * Kundenadressen, wo sie getrennt stehen, weil daran Geokodierung
             * und Fahrtzone haengen (D-024). Hier sind es vier Standorte, die
             * niemand auswertet.
             */
            $table->string('street')->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->string('city')->nullable();

            /*
             * Aussenansicht des Standorts im Object Storage (ADR-045), z. B.
             * `locations/Buchholz-outside.jpg`. Wie beim Mitarbeiterfoto nur
             * der Schluessel, nie die vollstaendige Adresse.
             */
            $table->string('photo_path')->nullable();

            /*
             * KEIN `is_active`. Ein eigener Standort ist entweder in Betrieb
             * oder er wird geloescht — ein stillgelegter, der weiter in Listen
             * steht, waere ein Zustand ohne Bedeutung (Nutzer).
             */
            $table->text('notes')->nullable();

            Columns::blame($table);
            $table->timestamps();
            $table->softDeletes();

            $table->index('name');
        });

        /*
         * Der Fremdschluessel steht hier und nicht in der `users`-Migration:
         * die laeuft zuerst, `sites` gibt es zu dem Zeitpunkt noch nicht. Die
         * Spalte selbst wird dort angelegt, damit `users` vollstaendig bleibt.
         */
        Schema::table('employees', function (Blueprint $table): void {
            /*
             * `restrictOnDelete`, nicht `nullOnDelete`: die Spalte ist NOT NULL.
             * Einen Standort mit Mitarbeitern zu loeschen lehnt ausserdem schon
             * `Core\Services\Sites` ab, mit einer Meldung statt eines
             * Datenbankfehlers.
             */
            $table->foreign('site_id')->references('id')->on('sites')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table): void {
            $table->dropForeign(['site_id']);
        });

        Schema::dropIfExists('sites');
    }
};
