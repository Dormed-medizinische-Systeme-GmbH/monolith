<?php

declare(strict_types=1);

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Modules\Core\Models\Employee;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Kalendervorschau — Entwurf, noch ohne Fachlichkeit.
 *
 * **Die Termine sind erfunden.** Es gibt weder eine `appointments`-Tabelle noch
 * ein Scheduling-Modul (`SCHEDULING.md`, Phase 6.1); diese Flaeche existiert,
 * um die Bedienung zu beurteilen, bevor das Schema steht. Genau deshalb sitzt
 * die Liste hier und nicht in einer Datenbank: sie ist Anschauungsmaterial und
 * soll auch so aussehen, wenn jemand den Code liest.
 *
 * Die Daten haengen an HEUTE statt an festen Datumsangaben — ein Entwurf, der
 * naechste Woche leer aussieht, laesst sich nicht beurteilen.
 */
final class CalendarController extends Controller
{
    public function __invoke(): Response
    {
        $employees = self::employees();

        return Inertia::render('erp/Calendar', [
            'employees' => $employees,
            'events' => self::platzhalter($employees),
        ]);
    }

    /**
     * Die Mitarbeiter fuer den Filter — echte Datensaetze samt Foto.
     *
     * Die Termine sind erfunden, die Personen nicht: nur so laesst sich
     * beurteilen, ob die Auswahl mit den vorhandenen Bildern trägt und ob 13
     * Eintraege im Menue noch bedienbar sind.
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
     * @param  list<array<string, mixed>>  $employees
     * @return list<array<string, mixed>>
     */
    private static function platzhalter(array $employees): array
    {
        $montag = Carbon::today()->startOfWeek();

        /** @var list<array{int, int, int, string, string, string}> */
        $vorlage = [
            // Tagesversatz ab Montag, Start, Dauer in Minuten, Titel, Ort, Farbe
            [0, 8, 90, 'Wartung Ultraschall', 'Musterpraxis Dr. Muster', 'blue'],
            [0, 10, 60, 'Einweisung Resona i9', 'Gemeinschaftspraxis Nord', 'green'],
            [0, 14, 120, 'Servicefall: Sonde defekt', 'Praxis Dr. Ahrens', 'red'],
            [1, 9, 45, 'Angebotstermin', 'Praxis am Markt', 'purple'],
            [1, 11, 30, 'Rückruf Frau Brehm', '—', 'gray'],
            [1, 13, 180, 'Wartungstour Hamburg', 'drei Praxen', 'orange'],
            [2, 8, 30, 'Teambesprechung', 'Buchholz', 'gray'],
            [2, 9, 240, 'Installation Neugerät', 'Gemeinschaftspraxis Nord', 'blue'],
            [3, 10, 60, 'Abnahme MPG', 'Musterpraxis Dr. Muster', 'green'],
            [3, 15, 90, 'Schulung Personal', 'Praxis am Markt', 'yellow'],
            [4, 8, 120, 'Servicefall: Bildstörung', 'Praxis Dr. Ahrens', 'red'],
            [4, 13, 60, 'Nachbesprechung Angebot', '—', 'purple'],
        ];

        $termine = [];

        foreach ($vorlage as $i => [$tag, $stunde, $dauer, $titel, $ort, $farbe]) {
            $start = $montag->copy()->addDays($tag)->setTime($stunde, 0);

            // Reihum auf die vorhandenen Mitarbeiter verteilt, damit der Filter
            // etwas zu filtern hat.
            $wer = $employees === [] ? null : $employees[$i % count($employees)];

            $termine[] = [
                'id' => 'e'.($i + 1),
                'title' => $titel,
                'location' => $ort,
                'description' => 'Platzhalter aus dem Entwurf — hier stünde später, worum es geht.',
                /*
                 * Aussentermin oder nicht. Spaeter faellt das weg: es ergibt
                 * sich aus der verknuepften Adresse — ein Termin bei einer
                 * Praxis ist ausser Haus, einer ohne ist es nicht. Bis dahin
                 * ist es ein Haken.
                 */
                'offsite' => ! in_array($ort, ['—', '', 'Buchholz'], true),
                'employeeId' => $wer['id'] ?? null,
                'assignee' => $wer['name'] ?? '—',
                'color' => $farbe,
                'allDay' => false,
                'start' => $start->toIso8601String(),
                'end' => $start->copy()->addMinutes($dauer)->toIso8601String(),
            ];
        }

        // Zwei mehrtaegige Eintraege — sie sind der Grund, warum eine Monatszelle
        // Positionen vergeben muss und nicht einfach untereinander stapelt.
        $termine[] = [
            'id' => 'u1',
            'title' => 'Urlaub',
            'location' => '',
            'description' => 'Genehmigt.',
            'offsite' => false,
            'employeeId' => $employees[0]['id'] ?? null,
            'assignee' => $employees[0]['name'] ?? '—',
            'color' => 'yellow',
            'allDay' => true,
            'start' => $montag->copy()->addDays(2)->startOfDay()->toIso8601String(),
            'end' => $montag->copy()->addDays(6)->endOfDay()->toIso8601String(),
        ];

        $termine[] = [
            'id' => 'm1',
            'title' => 'MEDICA Düsseldorf',
            'location' => 'Messe',
            'description' => 'Standbetreuung, Anreise am Vorabend.',
            'offsite' => true,
            'employeeId' => $employees[1]['id'] ?? null,
            'assignee' => $employees[1]['name'] ?? '—',
            'color' => 'orange',
            'allDay' => true,
            'start' => $montag->copy()->addDays(9)->startOfDay()->toIso8601String(),
            'end' => $montag->copy()->addDays(11)->endOfDay()->toIso8601String(),
        ];

        return $termine;
    }
}
