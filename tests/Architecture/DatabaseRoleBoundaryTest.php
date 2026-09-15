<?php

declare(strict_types=1);

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| Beweis fuer die Rollentrennung aus ADR-036
|--------------------------------------------------------------------------
|
| Die Architektur behauptet, die oeffentliche Website koenne Kundendaten nicht
| lesen — nicht weil es verboten waere, sondern weil die Verbindung sie nicht
| sieht. Diese Datei prueft das, statt es zu glauben.
|
*/

beforeEach(function (): void {
    requirePostgres();
});

test('die drei Anwendungsrollen existieren und sind entschaerft', function (string $connection): void {
    $role = config("database.connections.{$connection}.username");

    $row = DB::connection('pgsql_owner')->selectOne(
        'select rolsuper, rolbypassrls, rolcreaterole, rolcreatedb from pg_roles where rolname = ?',
        [$role],
    );

    expect($row)->not->toBeNull("Rolle {$role} fehlt — Migration create_database_roles gelaufen?");
    expect($row->rolsuper)->toBeFalse("{$role} ist SUPERUSER — RLS waere wirkungslos");
    expect($row->rolbypassrls)->toBeFalse("{$role} hat BYPASSRLS — RLS waere wirkungslos");
    expect($row->rolcreaterole)->toBeFalse();
    expect($row->rolcreatedb)->toBeFalse();
})->with(['pgsql', 'pgsql_customer', 'pgsql_public']);

test('die Anwendung verbindet sich nicht als Schema-Eigentuemer', function (): void {
    $owner = DB::connection('pgsql_owner')->getConfig('username');

    foreach (['pgsql', 'pgsql_customer', 'pgsql_public'] as $connection) {
        expect(DB::connection($connection)->getConfig('username'))
            ->not->toBe($owner, "{$connection} laeuft als Eigentuemer — Postgres wendet RLS darauf still nicht an");
    }
});

test('die oeffentliche Rolle kann employees nicht lesen', function (): void {
    expect(fn () => DB::connection('pgsql_public')->select('select * from employees limit 1'))
        ->toThrow(QueryException::class);
});

test('die Mitarbeiterrolle kann employees lesen', function (): void {
    expect(DB::connection('pgsql')->select('select * from employees limit 1'))->toBeArray();
});

test('die oeffentliche Rolle bekommt keine Default-Privileges auf neue Tabellen', function (): void {
    $owner = DB::connection('pgsql_owner');
    $public = config('database.connections.pgsql_public.username');

    $owner->statement('create table if not exists grant_probe (id bigint)');

    try {
        $granted = $owner->scalar(
            'select has_table_privilege(?, ?, ?)',
            [$public, 'grant_probe', 'SELECT'],
        );

        expect($granted)->toBeFalse(
            'Eine neue Tabelle ist fuer dormed_public automatisch lesbar — '.
            'ALTER DEFAULT PRIVILEGES darf diese Rolle nicht einschliessen (ADR-036)',
        );
    } finally {
        $owner->statement('drop table if exists grant_probe');
    }
});
