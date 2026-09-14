<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind different classes or traits.
|
*/

/*
 * `withoutVite()`: die Blade-Seiten der Website binden Assets ueber @vite ein.
 * Ohne das haengen die Tests am laufenden Vite-Dev-Server oder an einem
 * gebauten Manifest — beides hat mit dem zu Pruefenden nichts zu tun.
 */
pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->beforeEach(fn () => $this->withoutVite())
    ->in('Feature');

/*
 * Die Architektur-Suite bekommt bewusst KEIN RefreshDatabase: sie prueft
 * Postgres-Rollen, Grants und RLS (ADR-036) gegen die laufende Datenbank und
 * raeumt ihre Probe-Objekte selbst auf.
 */
pest()->extend(TestCase::class)->beforeEach(fn () => $this->withoutVite())->in('Architecture');

/**
 * Richtet die vier pgsql-Verbindungen auf die echte Testdatenbank aus und
 * ueberspringt den Test, wenn sie nicht erreichbar ist.
 *
 * Noetig, weil `phpunit.xml` DB_DATABASE global auf `:memory:` setzt — das
 * trifft sonst auch die Postgres-Verbindungen.
 */
function requirePostgres(): void
{
    $database = env('DB_PG_DATABASE', 'dormed');

    foreach (['pgsql', 'pgsql_customer', 'pgsql_public', 'pgsql_owner'] as $connection) {
        config(["database.connections.{$connection}.database" => $database]);
        DB::purge($connection);
    }

    /*
     * Die Architecture-Suite prueft die Umschaltung selbst und darf sie deshalb
     * nicht abgeschaltet lassen. Sie kommt hier ohne RefreshDatabase aus — das
     * ist der Grund, warum sie es sich leisten kann (siehe config/database.php).
     */
    config(['database.switch_by_access_point' => true]);

    try {
        DB::connection('pgsql_owner')->getPdo();
    } catch (Throwable $e) {
        test()->markTestSkipped(
            'PostgreSQL nicht erreichbar ('.$e->getMessage().'). '.
            'Dev-Stack starten: docker compose up -d'
        );
    }
}

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something()
{
    // ..
}
