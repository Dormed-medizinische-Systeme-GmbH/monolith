<?php

declare(strict_types=1);

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Modules\Core\Models\Employee;
use App\Modules\Crm\Models\Company;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Kalendervorschau — Entwurf, noch ohne Fachlichkeit.
 *
 * **Die Termine sind erfunden.** Es gibt weder eine `appointments`-Tabelle noch
 * ein Scheduling-Modul (`SCHEDULING.md`, Phase 6.1); diese Flaeche existiert,
 * um die Bedienung zu beurteilen, bevor das Schema steht. Genau deshalb sitzt
 * die Liste hier und nicht in einer Datenbank.
 *
 * Die Daten haengen an HEUTE statt an festen Datumsangaben — ein Entwurf, der
 * naechste Woche leer aussieht, laesst sich nicht beurteilen.
 *
 * **Ein Termin hat KEINEN Betreff.** Was im Kalender steht, wird aus Typ,
 * Status, Ort und verknuepfter Firma zusammengesetzt und nirgends gespeichert
 * (Nutzer, 2026-09-15). Das ist eine Revision von `SCHEDULING.md`, wo `title`
 * noch eine Spalte war — und zugleich die Rueckkehr des `Keyword`-Feldes aus
 * CAS, das dort als Datenfeld verworfen wurde: als abgeleiteter Wert ist es
 * richtig, als gespeicherter war es es nicht.
 */
final class CalendarController extends Controller
{
    public function __invoke(): Response
    {
        $employees = self::employees();
        $companies = self::companies();

        return Inertia::render('erp/Calendar', [
            'employees' => $employees,
            'companies' => $companies,
            'events' => self::platzhalter($employees, $companies),
        ]);
    }

    /**
     * Die Mitarbeiter fuer Filter und Zuordnung — echte Datensaetze samt Foto.
     *
     * Die Termine sind erfunden, die Personen nicht: nur so laesst sich
     * beurteilen, ob die Auswahl mit den vorhandenen Bildern traegt.
     *
     * @return list<array<string, mixed>>
     */
    private static function employees(): array
    {
        return Employee::query()
            ->where('is_active', true)
            ->orderBy('last_name')
            ->get()
            ->map(fn (Employee $employee): array => [
                'id' => $employee->id,
                'name' => $employee->name,
                'photoUrl' => $employee->photo_url,
            ])
            ->all();
    }

    /**
     * Firmen mit Anschrift — aus ihnen entsteht der Teil „bei X, PLZ Ort".
     *
     * @return list<array<string, mixed>>
     */
    private static function companies(): array
    {
        return Company::query()
            ->with('address')
            ->orderBy('name')
            ->get()
            ->map(fn (Company $company): array => [
                'id' => $company->id,
                'name' => $company->name,
                'postalCode' => $company->address?->postal_code,
                'city' => $company->address?->city,
            ])
            ->all();
    }

    /**
     * @param  list<array<string, mixed>>  $employees
     * @param  list<array<string, mixed>>  $companies
     * @return list<array<string, mixed>>
     */
    private static function platzhalter(array $employees, array $companies): array
    {
        $montag = Carbon::today()->startOfWeek();
        $firmen = array_column($companies, 'id');

        /** @var list<array{int, int, int, string, ?string, bool, ?int, string}> */
        $vorlage = [
            // Tagesversatz, Stunde, Dauer, Typ, Status, ausser Haus, Firma (Index), Notiz
            [0, 8, 90, 'wartung', 'bestaetigt', true, 0, 'Jahreswartung, Sonde 1 prüfen.'],
            [0, 10, 60, 'service', 'bestaetigt', true, 1, 'Einweisung des neuen Geräts.'],
            [0, 14, 120, 'service', 'vorlaeufig', true, 0, 'Sonde defekt, Ersatz mitnehmen.'],
            [1, 9, 45, 'besprechung', null, true, 1, 'Angebot vorstellen.'],
            [1, 11, 30, 'besprechung', null, false, null, 'Rückruf Frau Brehm.'],
            [1, 13, 180, 'wartung', 'bestaetigt', true, 1, 'Wartungstour, drei Geräte.'],
            [2, 8, 30, 'besprechung', null, false, null, 'Wochenrunde.'],
            [2, 9, 240, 'service', 'bestaetigt', true, 1, 'Installation Neugerät.'],
            [3, 10, 60, 'wartung', 'storniert', true, 0, 'Vom Kunden abgesagt.'],
            [3, 15, 90, 'service', 'vorlaeufig', true, 0, 'Schulung des Personals.'],
            [4, 8, 120, 'service', 'bestaetigt', true, 1, 'Bildstörung, Ursache unklar.'],
            [4, 13, 60, 'besprechung', null, false, null, 'Nachbesprechung Angebot.'],
        ];

        $termine = [];

        foreach ($vorlage as $i => [$tag, $stunde, $dauer, $typ, $status, $ausserHaus, $firma, $notiz]) {
            $start = $montag->copy()->addDays($tag)->setTime($stunde, 0);

            // Reihum auf die vorhandenen Mitarbeiter verteilt, damit der Filter
            // etwas zu filtern hat.
            $wer = $employees === [] ? null : $employees[$i % count($employees)];

            $termine[] = [
                'id' => 'e'.($i + 1),
                'type' => $typ,
                'status' => $status,
                'offsite' => $ausserHaus,
                'companyId' => $firma === null ? null : ($firmen[$firma] ?? null),
                'description' => $notiz,
                'employeeId' => $wer['id'] ?? null,
                'assignee' => $wer['name'] ?? '—',
                'allDay' => false,
                'start' => $start->toIso8601String(),
                'end' => $start->copy()->addMinutes($dauer)->toIso8601String(),
            ];
        }

        // Zwei mehrtaegige Eintraege — sie sind der Grund, warum eine Monatszelle
        // Positionen vergeben muss und nicht einfach untereinander stapelt.
        $termine[] = [
            'id' => 'u1',
            'type' => 'privat',
            'status' => null,
            'offsite' => false,
            'companyId' => null,
            'description' => 'Genehmigt.',
            'employeeId' => $employees[0]['id'] ?? null,
            'assignee' => $employees[0]['name'] ?? '—',
            'allDay' => true,
            'start' => $montag->copy()->addDays(2)->startOfDay()->toIso8601String(),
            'end' => $montag->copy()->addDays(6)->endOfDay()->toIso8601String(),
        ];

        $termine[] = [
            'id' => 'm1',
            'type' => 'besprechung',
            'status' => null,
            'offsite' => true,
            'companyId' => null,
            'description' => 'MEDICA, Standbetreuung.',
            'employeeId' => $employees[1]['id'] ?? null,
            'assignee' => $employees[1]['name'] ?? '—',
            'allDay' => true,
            'start' => $montag->copy()->addDays(9)->startOfDay()->toIso8601String(),
            'end' => $montag->copy()->addDays(11)->endOfDay()->toIso8601String(),
        ];

        return $termine;
    }
}
