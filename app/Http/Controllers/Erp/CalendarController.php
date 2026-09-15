<?php

declare(strict_types=1);

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
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
        return Inertia::render('erp/Calendar', [
            'events' => self::platzhalter(),
        ]);
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function platzhalter(): array
    {
        $montag = Carbon::today()->startOfWeek();

        /** @var list<array{int, int, int, string, string, string, string}> */
        $vorlage = [
            // Tagesversatz ab Montag, Start, Dauer in Minuten, Titel, Ort, Farbe, Zustaendiger
            [0, 8, 90, 'Wartung Ultraschall', 'Musterpraxis Dr. Muster', 'blue', 'R. Falk'],
            [0, 10, 60, 'Einweisung Resona i9', 'Gemeinschaftspraxis Nord', 'green', 'D. Kowol'],
            [0, 14, 120, 'Servicefall: Sonde defekt', 'Praxis Dr. Ahrens', 'red', 'E. Meitsch'],
            [1, 9, 45, 'Angebotstermin', 'Praxis am Markt', 'purple', 'M. Niewitz'],
            [1, 11, 30, 'Rückruf Frau Brehm', '—', 'gray', 'S. Ortmann'],
            [1, 13, 180, 'Wartungstour Hamburg', 'drei Praxen', 'orange', 'O. Hobbie'],
            [2, 8, 30, 'Teambesprechung', 'Buchholz', 'gray', 'alle'],
            [2, 9, 240, 'Installation Neugerät', 'Gemeinschaftspraxis Nord', 'blue', 'R. Falk'],
            [3, 10, 60, 'Abnahme MPG', 'Musterpraxis Dr. Muster', 'green', 'E. Meitsch'],
            [3, 15, 90, 'Schulung Personal', 'Praxis am Markt', 'yellow', 'D. Kowol'],
            [4, 8, 120, 'Servicefall: Bildstörung', 'Praxis Dr. Ahrens', 'red', 'O. Hobbie'],
            [4, 13, 60, 'Nachbesprechung Angebot', '—', 'purple', 'M. Niewitz'],
        ];

        $termine = [];

        foreach ($vorlage as $i => [$tag, $stunde, $dauer, $titel, $ort, $farbe, $wer]) {
            $start = $montag->copy()->addDays($tag)->setTime($stunde, 0);

            $termine[] = [
                'id' => 'e'.($i + 1),
                'title' => $titel,
                'location' => $ort,
                'assignee' => $wer,
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
            'title' => 'Urlaub B. Gopin',
            'location' => '',
            'assignee' => 'B. Gopin',
            'color' => 'yellow',
            'allDay' => true,
            'start' => $montag->copy()->addDays(2)->startOfDay()->toIso8601String(),
            'end' => $montag->copy()->addDays(6)->endOfDay()->toIso8601String(),
        ];

        $termine[] = [
            'id' => 'm1',
            'title' => 'MEDICA Düsseldorf',
            'location' => 'Messe',
            'assignee' => 'Vertrieb',
            'color' => 'orange',
            'allDay' => true,
            'start' => $montag->copy()->addDays(9)->startOfDay()->toIso8601String(),
            'end' => $montag->copy()->addDays(11)->endOfDay()->toIso8601String(),
        ];

        return $termine;
    }
}
