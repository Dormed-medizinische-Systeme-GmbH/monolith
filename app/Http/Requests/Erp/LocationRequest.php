<?php

declare(strict_types=1);

namespace App\Http\Requests\Erp;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Anlegen und Bearbeiten eines Kundenstandorts.
 *
 * Die Firma steht NICHT hier: sie kommt aus der Route (`/firmen/{company}/…`)
 * und ist damit nicht uebersteuerbar. Ein Formularfeld dafuer waere eine
 * Einladung, einen Standort in eine fremde Praxis zu schieben.
 *
 * Der Praxis-Netzwerk-Block (D-092, 14 Legacy-Felder) fehlt bewusst — er
 * gehoert zum Servicemodul und kommt mit ihm.
 */
final class LocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'is_primary' => ['required', 'boolean'],
            'notes' => ['nullable', 'string'],

            'street' => ['nullable', 'string', 'max:255'],
            'house_number' => ['nullable', 'string', 'max:32'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'city' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * Die Anschrift geht getrennt in die polymorphe `addresses` (D-003/D-014).
     *
     * @return array<string, mixed>
     */
    public function addressAttributes(): array
    {
        return array_filter($this->only(['street', 'house_number', 'postal_code', 'city']));
    }

    /**
     * @return array<string, mixed>
     */
    public function locationAttributes(): array
    {
        return $this->only(['name', 'is_primary', 'notes']);
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'Bezeichnung',
            'street' => 'Straße',
            'postal_code' => 'Postleitzahl',
            'city' => 'Ort',
        ];
    }
}
