<?php

declare(strict_types=1);

namespace App\Modules\Crm\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Der Portal-/Shop-Zugang eines CRM-Kontakts (ADR-037/042).
 *
 * Getrennt von `users`, und zwar strukturell: bei 1.600 Kundenkonten gegen 20
 * Mitarbeiterkonten in einer Tabelle waere das Einzige, was sie trennt, ein
 * `where`, das jemand vergessen kann.
 *
 * Verwaltet wird der Zugang vom ERP aus, direkt am Kontaktdatensatz — ein Kunde
 * ruft an, kennt weder Mail noch Passwort, und der Mitarbeiter setzt es ohne
 * Systemwechsel zurueck. Das ist der Grund fuer die ganze Konstruktion.
 *
 * @property string $id
 * @property string $person_id
 * @property string $email
 * @property bool $is_active
 */
final class CustomerAccount extends Authenticatable
{
    use HasUuids, Notifiable, SoftDeletes;

    protected $fillable = ['person_id', 'email', 'password', 'is_active'];

    protected $hidden = ['password', 'remember_token'];

    /**
     * @return BelongsTo<Person, $this>
     */
    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    /**
     * Die Firma, auf die RLS den Zugriff begrenzt (ADR-036).
     *
     * Solange ein Kontakt bei genau einer Company haengt, ist das eindeutig.
     * Haengt er an mehreren, ist es das nicht — die offene Frage 2 aus ADR-037.
     */
    public function companyId(): ?string
    {
        return $this->person?->contacts()->value('company_id');
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }
}
