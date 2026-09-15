<?php

declare(strict_types=1);

namespace App\Modules\Core\Services;

use App\Modules\Core\Models\Employee;
use RuntimeException;

/**
 * Schreibzugriffe auf Mitarbeiterdatensaetze.
 *
 * Liegt hier und nicht im Controller, weil an einem Mitarbeiter Regeln haengen,
 * die an jeder Schreibstelle gleich gelten muessen — und weil es bald mehr
 * werden: die Abteilungsmatrix (D-136/D-137) und die Uebernahme durch
 * Entra-SSO (D-029) greifen genau hier an.
 *
 * **Was hier NICHT steht, ist die Berechtigungsfrage.** Ob der handelnde
 * Mitarbeiter einen anderen anlegen DARF, entscheidet der Permission-Katalog —
 * der ist noch nicht gebaut. Bis dahin ist `auth:staff` die einzige Schranke,
 * und das ist ausdruecklich zu wenig (`.ai/rules/routing.md`).
 */
final class Employees
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public static function create(array $attributes): Employee
    {
        return Employee::query()->create(self::withoutEmptyPassword($attributes));
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public static function update(Employee $employee, array $attributes, ?Employee $actor = null): Employee
    {
        $attributes = self::withoutEmptyPassword($attributes);

        /*
         * Sich selbst stillzulegen ist die eine Aenderung, die man nicht
         * zuruecknehmen kann: ein inaktiver Mitarbeiter kommt nicht mehr herein
         * (Fortify::authenticateUsing), und ohne Berechtigungssystem gibt es
         * niemanden, der zwingend noch hereinkaeme. Das ist keine
         * Abteilungsregel, sondern ein Riegel gegen das Aussperren.
         */
        if ($actor !== null && $actor->is($employee) && array_key_exists('is_active', $attributes)
            && $attributes['is_active'] === false) {
            throw new RuntimeException('Der eigene Zugang kann nicht stillgelegt werden.');
        }

        $employee->update($attributes);

        return $employee;
    }

    public static function delete(Employee $employee, ?Employee $actor = null): void
    {
        if ($actor !== null && $actor->is($employee)) {
            throw new RuntimeException('Der eigene Zugang kann nicht geloescht werden.');
        }

        // `SoftDeletes` (D-018): der Datensatz bleibt, `TracksBlame` anderer
        // Tabellen zeigt weiter auf ihn und laeuft nicht ins Leere.
        $employee->delete();
    }

    /**
     * Ein leeres Passwortfeld bedeutet „nicht aendern", nicht „loeschen".
     *
     * Es darf ausserdem nie als leere Zeichenkette in die Spalte geraten: die
     * ist nullable, weil SSO-Nutzer spaeter gar keins haben (D-029) — und
     * `null` heisst „kein Passwortlogin", `''` hiesse ein Passwort, das
     * niemand kennt und das trotzdem gehasht wird.
     *
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    private static function withoutEmptyPassword(array $attributes): array
    {
        if (($attributes['password'] ?? null) === null || $attributes['password'] === '') {
            unset($attributes['password']);
        }

        return $attributes;
    }
}
