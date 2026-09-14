<?php

declare(strict_types=1);

namespace App\Modules\Crm\Models;

use App\Support\TracksBlame;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Realer Betriebs-/Servicestandort einer Company (D-007).
 *
 * Jede Company hat mindestens eine. Device und ServiceContract referenzieren
 * spaeter die LOCATION, nicht die Company — bei einer Praxis mit mehreren
 * Standorten waere sonst nicht bestimmbar, wo ein Geraet steht.
 *
 * Der Praxis-Netzwerk-Block (D-092) ersetzt 14 Legacy-Felder und haengt am
 * Standort, nicht am Geraet.
 *
 * @property int $id
 * @property int $company_id
 * @property string $name
 * @property bool $is_primary
 */
final class Location extends Model
{
    use SoftDeletes, TracksBlame;

    protected $fillable = [
        'company_id', 'name', 'notes', 'is_primary',
        'practice_software', 'practice_it_notes', 'network_server_user',
        'network_gateway', 'practice_hardware_asp', 'network_server_ip',
        'practice_it_asp', 'network_storage_path', 'network_server_password',
        'storage_ae_title', 'storage_port', 'network_subnet_mask',
        'worklist_port', 'worklist_ae_title',
    ];

    protected $hidden = ['network_server_password'];

    /**
     * @return BelongsTo<Company, $this>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * @return MorphOne<Address, $this>
     */
    public function address(): MorphOne
    {
        return $this->morphOne(Address::class, 'addressable');
    }

    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
            // Verschluesselt abgelegt: ein Praxis-Serverpasswort im Klartext in
            // der Datenbank waere auch mit RLS davor nicht vertretbar.
            'network_server_password' => 'encrypted',
        ];
    }
}
