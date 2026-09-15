<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;

/**
 * Wiederkehrende Spaltendefinitionen fuer Migrationen.
 *
 * Existiert vor allem wegen D-094: Enums werden als VARCHAR mit
 * CHECK-Constraint abgebildet, nicht als natives Postgres-ENUM. Grund ist
 * Aenderbarkeit — ein natives ENUM zu erweitern ist eine eigene Migration mit
 * Sperre, ein CHECK laesst sich ersetzen.
 *
 * Ohne diese Klasse stuende derselbe `DB::statement`-Dreizeiler in jeder
 * zweiten Migration, und irgendwann schriebe ihn jemand anders.
 */
final class Columns
{
    /**
     * `created_by` / `updated_by` fuer TracksBlame (D-018).
     *
     * Wird INNERHALB der `Schema::create`-Closure aufgerufen.
     */
    public static function blame(Blueprint $table): void
    {
        $table->foreignUuid('created_by')->nullable()->constrained('employees')->nullOnDelete();
        $table->foreignUuid('updated_by')->nullable()->constrained('employees')->nullOnDelete();
    }

    /**
     * CHECK-Constraint auf die erlaubten Enum-Werte (D-094).
     *
     * Wird NACH `Schema::create` aufgerufen — innerhalb der Closure existiert
     * die Tabelle noch nicht, ein `ALTER TABLE` liefe dort ins Leere.
     *
     * @param  list<string>  $values
     */
    public static function check(string $table, string $column, array $values): void
    {
        $list = collect($values)
            ->map(fn (string $value): string => "'".str_replace("'", "''", $value)."'")
            ->implode(', ');

        DB::statement(
            "ALTER TABLE \"{$table}\" ADD CONSTRAINT \"{$table}_{$column}_check\" CHECK (\"{$column}\" IN ({$list}))"
        );
    }
}
