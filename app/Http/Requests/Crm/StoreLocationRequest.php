<?php

namespace App\Http\Requests\Crm;

use App\Http\Requests\Crm\Concerns\ValidatesAddress;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreLocationRequest extends FormRequest
{
    use ValidatesAddress;

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:5000'],
            ...$this->addressRules(),
        ];
    }
}
