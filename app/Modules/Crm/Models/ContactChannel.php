<?php

declare(strict_types=1);

namespace App\Modules\Crm\Models;

use App\Modules\Crm\Enums\ChannelLabel;
use App\Modules\Crm\Enums\ChannelType;
use App\Support\TracksBlame;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Ersetzt die rund 30 nummerierten Legacy-Kommunikationsslots (D-010).
 * Polymorph an Company ODER Person.
 *
 * @property string $id
 * @property ChannelType $channel_type
 * @property ChannelLabel $label
 * @property string $value
 * @property bool $is_primary
 */
final class ContactChannel extends Model
{
    use HasUuids, SoftDeletes, TracksBlame;

    protected $fillable = ['channel_type', 'label', 'value', 'is_primary'];

    /**
     * @return MorphTo<Model, $this>
     */
    public function channelable(): MorphTo
    {
        return $this->morphTo();
    }

    protected function casts(): array
    {
        return [
            'channel_type' => ChannelType::class,
            'label' => ChannelLabel::class,
            'is_primary' => 'boolean',
        ];
    }
}
