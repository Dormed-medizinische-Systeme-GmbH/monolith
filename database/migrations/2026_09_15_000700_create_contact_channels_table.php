<?php

declare(strict_types=1);

use App\Support\Columns;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ersetzt die rund 30 nummerierten Legacy-Kommunikationsslots (D-010).
 * Polymorph an Company ODER Person.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_channels', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuidMorphs('channelable');
            $table->string('channel_type');

            // Fester Enum, im Gegensatz zu `company_contacts.role`
            // auswertungsrelevant — deshalb hier ein CHECK.
            $table->string('label');

            $table->string('value');

            // Je (channelable, channel_type) hoechstens einer.
            $table->boolean('is_primary')->default(false);

            Columns::blame($table);
            $table->timestamps();
            $table->softDeletes();
        });

        Columns::check('contact_channels', 'channel_type', ['phone', 'mobile', 'fax', 'email', 'web']);
        Columns::check('contact_channels', 'label', [
            'geschaeftlich', 'praxis', 'zentrale', 'durchwahl', 'rechnungsversand',
            'privat', 'mobil_persoenlich', 'mobil_arzt', 'homepage', 'sonstige',
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_channels');
    }
};
