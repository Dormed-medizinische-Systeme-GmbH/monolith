<?php

declare(strict_types=1);

namespace App\Http\Requests\Erp;

use App\Modules\Core\Models\Site;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Anlegen und Bearbeiten eines eigenen Standorts.
 */
final class SiteRequest extends FormRequest
{
    /**
     * Gehoert dem Permission-Katalog (D-136), sobald er existiert.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $site = $this->route('site');

        return [
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique(Site::class, 'name')->ignore($site instanceof Site ? $site->id : null),
            ],
            // Die Anschrift darf vorerst fehlen: ein Standort entsteht manchmal,
            // bevor die Adresse feststeht. Strasse und Hausnummer stehen in EINEM
            // Feld — es sind vier Standorte, die niemand auswertet.
            'street' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'city' => ['nullable', 'string', 'max:255'],

            'notes' => ['nullable', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'Name',
            'street' => 'Straße',
            'postal_code' => 'Postleitzahl',
            'city' => 'Ort',
        ];
    }
}
