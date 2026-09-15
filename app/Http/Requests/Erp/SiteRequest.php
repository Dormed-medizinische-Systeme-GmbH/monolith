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
            'short_name' => ['nullable', 'string', 'max:32'],

            // Die Anschrift darf vorerst fehlen: ein Standort entsteht manchmal,
            // bevor die Adresse feststeht.
            'street' => ['nullable', 'string', 'max:255'],
            'house_number' => ['nullable', 'string', 'max:32'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'city' => ['nullable', 'string', 'max:255'],

            'notes' => ['nullable', 'string'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'Name',
            'short_name' => 'Kürzel',
            'street' => 'Straße',
            'house_number' => 'Hausnummer',
            'postal_code' => 'Postleitzahl',
            'city' => 'Ort',
        ];
    }
}
