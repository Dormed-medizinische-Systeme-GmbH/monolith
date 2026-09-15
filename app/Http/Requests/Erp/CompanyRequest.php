<?php

declare(strict_types=1);

namespace App\Http\Requests\Erp;

use App\Modules\Core\Models\User;
use App\Modules\Crm\Models\Company;
use App\Modules\Crm\Models\MedicalSpecialty;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Anlegen und Bearbeiten einer Firma.
 */
final class CompanyRequest extends FormRequest
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
        $company = $this->route('company');
        $id = $company instanceof Company ? $company->id : null;

        return [
            'name' => ['required', 'string', 'max:255'],
            'name_addition' => ['nullable', 'string', 'max:255'],
            'medical_specialty_id' => [
                'nullable',
                Rule::exists(MedicalSpecialty::class, 'id')->where('is_active', true),
            ],
            'debitor_number' => ['nullable', 'string', 'max:255'],

            /*
             * Zustaendige Mitarbeiter. Nur AKTIVE: ein stillgelegter Zugang
             * gehoert nicht mehr ins Haus, und die Spec blendet ihn
             * ausdruecklich aus den `responsible_*`-Feldern aus
             * (IDENTITY_RBAC.md).
             *
             * Informativ, KEINE Berechtigung (D-016).
             */
            'responsible_sales_id' => [
                'nullable',
                Rule::exists(User::class, 'id')->where('is_active', true),
            ],
            'responsible_service_id' => [
                'nullable',
                Rule::exists(User::class, 'id')->where('is_active', true),
            ],

            // Abweichender Rechnungsempfaenger, nie die Firma selbst (D-004/D-066).
            'billing_company_id' => [
                'nullable',
                Rule::exists(Company::class, 'id'),
                Rule::notIn(array_filter([$id])),
            ],

            'avv_status' => ['required', Rule::in(['none', 'signed'])],
            'avv_signed_at' => ['nullable', 'date'],

            'legal_form' => ['nullable', 'string', 'max:255'],
            'tax_number' => ['nullable', 'string', 'max:255'],
            'vat_id' => ['nullable', 'string', 'max:255'],

            'iban' => ['nullable', 'string', 'max:34'],
            'bic' => ['nullable', 'string', 'max:11'],
            'bank_account_holder' => ['nullable', 'string', 'max:255'],
            'bank_name' => ['nullable', 'string', 'max:255'],

            'notes' => ['nullable', 'string'],

            'street' => ['nullable', 'string', 'max:255'],
            'house_number' => ['nullable', 'string', 'max:32'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'city' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function companyAttributes(): array
    {
        return $this->safe()->except(['street', 'house_number', 'postal_code', 'city']);
    }

    /**
     * @return array<string, mixed>
     */
    public function addressAttributes(): array
    {
        return array_filter($this->only(['street', 'house_number', 'postal_code', 'city']));
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'Name',
            'name_addition' => 'Namenszusatz',
            'medical_specialty_id' => 'Fachrichtung',
            'debitor_number' => 'Kundennummer',
            'responsible_sales_id' => 'Zuständig Vertrieb',
            'responsible_service_id' => 'Zuständig Service',
            'billing_company_id' => 'Rechnungsempfänger',
            'avv_status' => 'AVV',
        ];
    }
}
