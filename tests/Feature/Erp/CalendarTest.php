<?php

declare(strict_types=1);

use App\Modules\Core\Models\Employee;
use Carbon\Carbon;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SiteSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function (): void {
    (new RoleSeeder)->run();
    (new SiteSeeder)->run();

    // Mehrere, damit die Rundverteilung der Platzhaltertermine ueberhaupt
    // verteilen kann — mit einem einzigen haengt alles an derselben Person.
    $this->ich = Employee::factory()->create();
    Employee::factory()->count(3)->create();
});

test('der Kalender verlangt eine Anmeldung', function (): void {
    $this->get('http://'.config('domains.erp').'/kalender')
        ->assertRedirect('http://'.config('domains.erp').'/login');
});

test('er liefert Platzhaltertermine rund um heute', function (): void {
    /*
     * Die Termine haengen an HEUTE statt an festen Datumsangaben — ein Entwurf,
     * der naechste Woche leer aussieht, laesst sich nicht beurteilen.
     */
    $this->actingAs($this->ich, 'staff')
        ->get('http://'.config('domains.erp').'/kalender')
        ->assertOk()
        ->assertInertia(function (Assert $page): void {
            $page->component('erp/Calendar');

            $events = collect($page->toArray()['props']['events']);

            expect($events)->not->toBeEmpty();

            // Jeder Termin haengt an einem echten Mitarbeiter — sonst haette
            // der Personenfilter nichts zu filtern.
            $employees = collect($page->toArray()['props']['employees']);

            expect($employees)->not->toBeEmpty();
            expect($employees->first())->toHaveKeys(['id', 'name', 'photoUrl']);
            expect($events->pluck('employeeId')->filter()->unique()->count())
                ->toBeGreaterThan(1);
            expect($events->pluck('employeeId')->filter()->diff($employees->pluck('id')))
                ->toBeEmpty();

            $dieseWoche = $events->filter(fn (array $e): bool => Carbon::parse($e['start'])
                ->between(now()->startOfWeek(), now()->endOfWeek()));

            expect($dieseWoche)->not->toBeEmpty();

            // Mehrtaegige sind der Grund, warum eine Monatszelle Positionen
            // vergeben muss und nicht einfach stapelt.
            expect($events->where('allDay', true))->not->toBeEmpty();

            /*
             * KEIN Betreff: was im Kalender steht, wird aus Typ, Status,
             * „ausser Haus" und der verknuepften Firma zusammengesetzt und
             * nirgends gespeichert.
             */
            expect($events->first())->not->toHaveKey('title');
            expect($events->first())->toHaveKeys(['type', 'status', 'offsite', 'companyId']);

            // Typen ohne Status muessen einen leeren tragen, nicht irgendeinen.
            expect($events->where('type', 'besprechung')->pluck('status')->filter())
                ->toBeEmpty();

            // Alle drei Status kommen im Entwurf vor, damit sich die
            // Praefixe [BLOCKED] und [STORNO] beurteilen lassen.
            expect($events->pluck('status')->filter()->unique()->sort()->values()->all())
                ->toBe(['bestaetigt', 'storniert', 'vorlaeufig']);
        });
});
