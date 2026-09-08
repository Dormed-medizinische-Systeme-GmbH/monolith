<?php

namespace App\Http\Requests\Crm\Concerns;

trait ValidatesAddress
{
    /**
     * Rules for the optional nested `address` payload. Street, postal code and
     * city are all-or-nothing; leave every field blank to remove the address.
     *
     * @return array<string, array<int, string>>
     */
    protected function addressRules(): array
    {
        return [
            'address' => ['sometimes', 'array'],
            'address.street' => ['nullable', 'string', 'max:255', 'required_with:address.postal_code,address.city'],
            'address.house_number' => ['nullable', 'string', 'max:32'],
            'address.postal_code' => ['nullable', 'string', 'max:20', 'required_with:address.street,address.city'],
            'address.city' => ['nullable', 'string', 'max:255', 'required_with:address.street,address.postal_code'],
            'address.country_code' => ['nullable', 'string', 'size:2'],
        ];
    }
}
