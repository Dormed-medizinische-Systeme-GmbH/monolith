<?php

declare(strict_types=1);

namespace App\Http\Requests\Erp;

use App\Modules\Core\Models\Employee;
use App\Modules\Core\Models\Role;
use App\Modules\Core\Models\Site;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Anlegen und Bearbeiten eines Mitarbeiters.
 *
 * Eine Klasse fuer beides: die Regeln unterscheiden sich nur darin, ob der
 * eigene Datensatz von der Eindeutigkeitspruefung ausgenommen wird und ob ein
 * Passwort ueberhaupt erwartet werden darf.
 */
final class EmployeeRequest extends FormRequest
{
    /**
     * Die Berechtigungsfrage gehoert hierher, sobald der Permission-Katalog
     * existiert (D-136). Bis dahin ist `auth:staff` in der Route die einzige
     * Schranke — bewusst zu wenig, aber sichtbar zu wenig.
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
        $employee = $this->route('employee');

        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 'email', 'max:255',
                Rule::unique(Employee::class, 'email')->ignore($employee instanceof Employee ? $employee->id : null),
            ],

            /*
             * Nullable, weil ein Mitarbeiter ohne Passwort ein gueltiger
             * Zustand ist: angelegt, aber noch nicht freigeschaltet — und
             * spaeter der Normalfall, wenn Entra-SSO uebernimmt (D-029).
             */
            'password' => ['nullable', 'string', 'min:8'],

            // NOT NULL (D-124): genau eine Rolle. Und nur eine aktive — eine
            // stillgelegte Rolle zuzuweisen hiesse, jemanden ohne Rechte anzulegen.
            'role_id' => [
                'required',
                Rule::exists(Role::class, 'id')->where('is_active', true),
            ],

            /*
             * PFLICHT (Nutzer): jeder Mitarbeiter gehoert zu einer
             * Betriebsstaette, auch wer ueberwiegend unterwegs ist. Ein
             * Standort ohne Zuordnung waere eine Liste, die nie vollstaendig ist.
             */
            'site_id' => ['required', Rule::exists(Site::class, 'id')],

            'is_active' => ['required', 'boolean'],

            // Bootstrap-/IT-Bypass (D-028), kein Ersatz fuer eine Rolle.
            'is_admin' => ['required', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'first_name' => 'Vorname',
            'last_name' => 'Nachname',
            'email' => 'E-Mail-Adresse',
            'password' => 'Passwort',
            'role_id' => 'Rolle',
            'site_id' => 'Standort',
        ];
    }
}
