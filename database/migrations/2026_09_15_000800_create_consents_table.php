<?php

declare(strict_types=1);

use App\Support\Columns;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Historisierte DSGVO-Einwilligung je Person und Kanal (D-013).
 *
 * Wird NIE hart geloescht — Nachweispflicht. Ein Statuswechsel erzeugt einen
 * neuen Datensatz, er aendert keinen bestehenden.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consents', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->foreignUuid('person_id')->constrained('people')->cascadeOnDelete();
            $table->string('channel');
            $table->string('status');
            $table->dateTime('granted_at')->nullable();
            $table->dateTime('revoked_at')->nullable();
            $table->string('source');

            Columns::blame($table);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['person_id', 'channel']);
        });

        Columns::check('consents', 'channel', ['fax', 'mail', 'post', 'sms', 'telefon']);
        Columns::check('consents', 'status', ['erteilt', 'widerrufen']);
        Columns::check('consents', 'source', ['formular', 'muendlich', 'telefonisch', 'import', 'sonstige']);
    }

    public function down(): void
    {
        Schema::dropIfExists('consents');
    }
};
