<?php

namespace App\Http\Requests\Crm;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCompanyContactRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'person_id' => [
                'required',
                Rule::exists('people', 'id'),
                Rule::unique('company_contacts', 'person_id')
                    ->where('company_id', $this->route('company')->id),
            ],
            'role' => ['nullable', 'string', 'max:255'],
            'is_primary' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'person_id.unique' => 'Diese Person ist bereits Kontakt dieser Company.',
        ];
    }
}
