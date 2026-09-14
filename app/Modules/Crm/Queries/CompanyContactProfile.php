<?php

declare(strict_types=1);

namespace App\Modules\Crm\Queries;

use App\Modules\Crm\Models\CompanyContact;
use App\Modules\Crm\Models\Consent;
use App\Modules\Crm\Models\ContactChannel;
use Illuminate\Database\Eloquent\Collection;

/**
 * Ein Ansprechpartner — also eine Person IN einer bestimmten Firma.
 *
 * Der Rahmen ist die Beziehung (`company_contacts`), nicht die Person: Rolle
 * und Abteilung gelten gegenueber dieser Firma (D-005), und dieselbe Person
 * kann anderswo eine andere Rolle haben. Was dagegen an der Person haengt —
 * Name, Kommunikationswege, Einwilligungen, Portalzugang — gilt ueberall
 * gleich und ist deshalb hier als `person` ausgewiesen.
 */
final class CompanyContactProfile
{
    /**
     * @return array<string, mixed>
     */
    public static function for(CompanyContact $contact): array
    {
        $contact->load([
            'company',
            'person.customerAccount',
            'person.contactChannels',
            'person.consents',
            'person.companies',
        ]);

        $person = $contact->person;
        $account = $person->customerAccount;

        return [
            'id' => $contact->id,
            'role' => $contact->role,
            'department' => $contact->department,
            'isPrimary' => $contact->is_primary,

            'company' => [
                'id' => $contact->company->id,
                'name' => $contact->company->name,
            ],

            'person' => [
                'id' => $person->id,
                'name' => trim(implode(' ', array_filter([
                    $person->name_suffix, $person->title, $person->first_name, $person->last_name,
                ]))),
                'stammdaten' => array_filter([
                    'Vorname' => $person->first_name,
                    'Nachname' => $person->last_name,
                    'Titel' => $person->title,
                    'Namenszusatz' => $person->name_suffix,
                    'Geschlecht' => $person->gender->label(),
                    'Sprache' => $person->locale,
                ], fn (?string $value): bool => $value !== null && $value !== ''),
            ],

            'channels' => self::channels($person->contactChannels),

            'account' => $account === null ? null : [
                'email' => $account->email,
                'active' => $account->is_active,
                'lastLoginAt' => $account->last_login_at?->format('d.m.Y H:i'),
                'verifiedAt' => $account->email_verified_at?->format('d.m.Y'),
            ],

            /*
             * Die uebrigen Firmen derselben Person. Steht hier, weil der Fall
             * sonst unsichtbar bleibt: in dieser Ansicht sieht eine Person mit
             * zwei Praxen genauso aus wie eine mit einer — und genau daran
             * haengt die offene Frage 2 aus ADR-037.
             */
            'weitereFirmen' => $person->companies
                ->reject(fn ($company): bool => $company->id === $contact->company_id)
                ->values()
                ->map(fn ($company): array => [
                    'id' => $company->id,
                    'name' => $company->name,
                    'role' => $company->pivot->role,
                ])
                ->all(),

            'consents' => self::consents($person->consents),
        ];
    }

    /**
     * Der aktuelle Stand je Kanal.
     *
     * Einwilligungen werden nie geaendert, sondern fortgeschrieben (D-013):
     * ein Widerruf ist ein NEUER Datensatz. Massgeblich ist deshalb der
     * juengste je Kanal; die aelteren sind der Nachweis und gehoeren in eine
     * Historie, nicht in die Uebersicht.
     *
     * @param  Collection<int, Consent>  $consents
     * @return list<array<string, mixed>>
     */
    private static function consents(Collection $consents): array
    {
        return $consents
            /*
             * `created_at` allein reicht nicht: zwei Eintraege desselben Kanals
             * koennen auf dieselbe Sekunde fallen — bei einem Import sogar auf
             * denselben Zeitpunkt —, und dann entscheidet der Zufall, welcher
             * als aktuell gilt. Der Schluessel bricht den Gleichstand, weil
             * UUIDv7 zeitgeordnet ist (ADR-046) und damit die Reihenfolge der
             * Anlage kennt.
             */
            ->sortByDesc(fn (Consent $consent): string => $consent->created_at?->format('Y-m-d H:i:s').'|'.$consent->id)
            ->unique(fn (Consent $consent): string => $consent->channel->value)
            ->sortBy(fn (Consent $consent): string => $consent->channel->label())
            ->values()
            ->map(fn (Consent $consent): array => [
                'id' => $consent->id,
                'channel' => $consent->channel->label(),
                'status' => $consent->status->label(),
                'granted' => $consent->status->value === 'erteilt',
                'date' => ($consent->revoked_at ?? $consent->granted_at)?->format('d.m.Y'),
                'source' => $consent->source->label(),
            ])
            ->all();
    }

    /**
     * @param  Collection<int, ContactChannel>  $channels
     * @return list<array<string, mixed>>
     */
    private static function channels(Collection $channels): array
    {
        return $channels
            ->sortByDesc('is_primary')
            ->values()
            ->map(fn (ContactChannel $channel): array => [
                'id' => $channel->id,
                'type' => $channel->channel_type->value,
                'typeLabel' => $channel->channel_type->label(),
                'label' => $channel->label->label(),
                'value' => $channel->value,
                'isPrimary' => $channel->is_primary,
            ])
            ->all();
    }
}
