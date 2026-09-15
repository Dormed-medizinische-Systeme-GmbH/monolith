<?php

declare(strict_types=1);

namespace App\Http\Requests\Erp;

use App\Modules\Core\Models\Employee;
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
        /** @var Company|null $company */
        $company = $this->route('company') instanceof Company ? $this->route('company') : null;
        $id = $company?->id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'name_addition' => ['nullable', 'string', 'max:255'],
            'medical_specialty_id' => [
                'nullable',
                Rule::exists(MedicalSpecialty::class, 'id')->where('is_active', true),
            ],
            'debitor_number' => ['nullable', 'string', 'max:255'],

            /*
             * Zustaendige Mitarbeiter, je aus IHRER Abteilung: fuer den
             * Vertrieb nur `sales`, fuer den Service nur `service`. Die Rolle
             * ist die einzige Quelle dafuer, wer wozu gehoert (D-124) — eine
             * zweite Liste daneben liefe unweigerlich auseinander.
             *
             * **Pflichtfelder in der Maske, aber nullable in der Spalte.** Jede
             * Praxis, die jemand anlegt, bekommt einen Verantwortlichen. Die
             * Spalte bleibt trotzdem offen, weil die Uebernahme aus CAS
             * Datensaetze bringen wird, denen die Angabe fehlt — ein NOT NULL
             * zwaenge dazu, beim Import jemanden zu erfinden.
             *
             * Nur AKTIVE: ein stillgelegter Zugang gehoert nicht mehr ins Haus
             * und wird aus den `responsible_*`-Feldern ausgeblendet
             * (IDENTITY_RBAC.md).
             *
             * Informativ, KEINE Berechtigung (D-016).
             */
            'responsible_sales_id' => [
                'required',
                Rule::in(self::auswaehlbar('sales', $company?->responsible_sales_id)),
            ],
            'responsible_service_id' => [
                'required',
                Rule::in(self::auswaehlbar('service', $company?->responsible_service_id)),
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
     * Wer fuer diese Abteilung in Frage kommt.
     *
     * Der BISHERIGE Zustaendige bleibt zulaessig, auch wenn er die Abteilung
     * gewechselt hat oder ausgeschieden ist. Sonst schluege jedes Speichern
     * fehl, solange niemand den Nachfolger benannt hat — und man koennte an der
     * Firma nicht einmal die Anschrift aendern, ohne zuerst eine Personalfrage
     * zu klaeren.
     *
     * @return list<string>
     */
    public static function auswaehlbar(string $rolle, ?string $bisher = null): array
    {
        $ids = Employee::query()
            ->where('is_active', true)
            ->whereRelation('role', 'key', $rolle)
            ->pluck('id')
            ->all();

        return array_values(array_unique(array_filter([...$ids, $bisher])));
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
            'responsible_sales_id' => 'Verantwortlicher (Vertrieb)',
            'responsible_service_id' => 'Verantwortlicher (Service)',
            'billing_company_id' => 'Rechnungsempfänger',
            'avv_status' => 'AVV',
        ];
    }
}
