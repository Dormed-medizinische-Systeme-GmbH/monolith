<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/**
 * Die Ausgangsbasis der Anwendung.
 *
 * KEIN Demo-Seed. Was hier laeuft, laeuft im Dev-Stack bei jedem `up` (weil
 * alles fluechtig ist, ADR-041) und in Produktion EINMAL, um den Grundbestand
 * herzustellen. Genau deshalb bleibt der Lauf dauerhaft funktionsfaehig: er
 * wird staendig ausgefuehrt.
 *
 * Alles hier ist idempotent — ein zweiter Lauf aendert nichts.
 */
final class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            // Ohne Rollen kein Mitarbeiter: `employees.role_id` ist NOT NULL (D-124).
            RoleSeeder::class,
            // Ohne Fachrichtungen kein Fachgebiet-Feld im Kontaktformular (D-133).
            MedicalSpecialtySeeder::class,
            // Die eigenen Standorte — Grundbestand, kein Beispiel.
            SiteSeeder::class,
            // Dateibestand in den Object Storage (ADR-045).
            ObjectStorageSeeder::class,
            // Zwei Anmeldedaten zum Ausprobieren — ueberspringt sich in Produktion.
            DevelopmentAccountSeeder::class,
            // Ein kleiner Artikelkatalog — ueberspringt sich ebenfalls.
            ArticleCatalogSeeder::class,
        ]);
    }
}
