<?php

declare(strict_types=1);

use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| Beweis fuer die RLS-Mechanik aus ADR-036
|--------------------------------------------------------------------------
|
| Prueft die MECHANIK an einer Probe-Tabelle, nicht an Fachdaten: dass eine
| Policy ueber eine Session-Variable greift, dass sie fail-closed ist, und dass
| die Mitarbeiterrolle unberuehrt bleibt. Wenn die Kundenflaechen entstehen,
| kommt derselbe Test noch einmal gegen echte Tabellen.
|
| `NULLIF(..., '')` in der Policy ist Pflicht, nicht Kosmetik: eine leere
| Zeichenkette — was eine Middleware ohne Firmenkontext setzt — wuerde bei
| `''::bigint` einen Fehler werfen statt null Zeilen zu liefern.
|
*/

beforeEach(function (): void {
    requirePostgres();

    $owner = DB::connection('pgsql_owner');
    $customer = config('database.connections.pgsql_customer.username');
    $staff = config('database.connections.pgsql.username');

    $owner->statement('drop table if exists rls_probe');
    $owner->statement('create table rls_probe (id bigserial primary key, company_id bigint not null, secret text not null)');
    $owner->statement('alter table rls_probe enable row level security');
    $owner->statement("grant select on rls_probe to \"{$customer}\", \"{$staff}\"");

    $owner->statement("create policy customer_own_company on rls_probe for select to \"{$customer}\"
        using (company_id = nullif(current_setting('app.company_id', true), '')::bigint)");
    $owner->statement("create policy staff_full_access on rls_probe for select to \"{$staff}\" using (true)");

    $owner->table('rls_probe')->insert([
        ['company_id' => 1, 'secret' => 'gehoert Firma 1'],
        ['company_id' => 2, 'secret' => 'gehoert Firma 2'],
    ]);

    DB::purge('pgsql_customer');
    DB::purge('pgsql');
});

afterEach(function (): void {
    DB::connection('pgsql_owner')->statement('drop table if exists rls_probe');
});

/**
 * Setzt den Firmenkontext so, wie die Middleware es tut: sitzungsweit.
 */
function setCompanyContext(?string $companyId): void
{
    $pdo = DB::connection('pgsql_customer')->getPdo();

    $pdo->exec(sprintf(
        'select set_config(%s, %s, false)',
        $pdo->quote('app.company_id'),
        $pdo->quote((string) $companyId),
    ));
}

test('ohne Firmenkontext sieht der Kunde null Zeilen, nicht alle', function (): void {
    $rows = DB::connection('pgsql_customer')->table('rls_probe')->get();

    expect($rows)->toHaveCount(0);
});

test('ein leerer Firmenkontext faellt ebenfalls geschlossen aus', function (): void {
    setCompanyContext('');

    expect(DB::connection('pgsql_customer')->table('rls_probe')->get())->toHaveCount(0);
});

test('mit Firmenkontext sieht der Kunde genau die eigenen Zeilen', function (): void {
    setCompanyContext('1');

    $rows = DB::connection('pgsql_customer')->table('rls_probe')->get();

    expect($rows)->toHaveCount(1);
    expect($rows->first()->secret)->toBe('gehoert Firma 1');
});

test('Kunde A sieht die Zeilen von Kunde B nicht', function (): void {
    setCompanyContext('2');

    $secrets = DB::connection('pgsql_customer')->table('rls_probe')->pluck('secret');

    expect($secrets)->not->toContain('gehoert Firma 1');
    expect($secrets)->toContain('gehoert Firma 2');
});

test('die Mitarbeiterrolle bleibt von der Kunden-Policy unberuehrt', function (): void {
    expect(DB::connection('pgsql')->table('rls_probe')->get())->toHaveCount(2);
});
