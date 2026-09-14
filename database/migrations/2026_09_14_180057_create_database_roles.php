<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Legt die drei Anwendungsrollen aus ADR-036 an.
 *
 * Rollen sind Cluster-Objekte, keine Schema-Objekte — sie hier zu fuehren ist
 * trotzdem richtig: so entstehen sie im Dev-Stack bei jedem `up` und beim
 * Produktiv-Deploy von selbst, statt als handischer Schritt, den irgendwann
 * jemand vergisst.
 *
 * Laeuft ausschliesslich ueber die Eigentuemer-Verbindung:
 *
 *     php artisan migrate --database=pgsql_owner
 *
 * Die Anwendung selbst verbindet sich nie so. Postgres wendet RLS auf den
 * Tabelleneigentuemer nicht an, und zwar still und ohne Fehlermeldung.
 */
return new class extends Migration
{
    /**
     * Rollen ohne Default-Privileges. `dormed_public` steht hier bewusst nicht
     * drin: es bekommt jede Tabelle einzeln und absichtlich freigegeben, damit
     * eine neue Migration der oeffentlichen Website standardmaessig nichts oeffnet.
     *
     * @var list<string>
     */
    private const GRANTED_BY_DEFAULT = ['staff', 'customer'];

    /**
     * @var list<string>
     */
    private const ALL_ROLES = ['staff', 'customer', 'public'];

    public function up(): void
    {
        if (! $this->onPostgres()) {
            return;
        }

        $database = $this->quoteIdentifier(DB::connection()->getDatabaseName());
        $owner = $this->quoteIdentifier((string) DB::connection()->getConfig('username'));

        /*
         * Postgres gibt JEDER neuen Datenbank CONNECT an die Pseudo-Rolle PUBLIC
         * (sichtbar als fuehrendes `=Tc/owner` in `pg_database.datacl`). Ohne
         * diesen Widerruf waere das GRANT CONNECT unten wirkungslose Kosmetik:
         * verbinden darf ohnehin jeder, der irgendein Login hat.
         *
         * Gleiches gilt fuer USAGE auf dem Schema `public`.
         */
        DB::statement("REVOKE CONNECT ON DATABASE {$database} FROM PUBLIC");
        DB::statement('REVOKE USAGE ON SCHEMA public FROM PUBLIC');

        foreach (self::ALL_ROLES as $key) {
            $role = $this->roleName($key);
            $quoted = $this->quoteIdentifier($role);
            $password = DB::getPdo()->quote($this->rolePassword($key));

            $attributes = 'LOGIN NOSUPERUSER NOCREATEDB NOCREATEROLE NOBYPASSRLS';

            DB::statement($this->roleExists($role)
                ? "ALTER ROLE {$quoted} WITH {$attributes} PASSWORD {$password}"
                : "CREATE ROLE {$quoted} WITH {$attributes} PASSWORD {$password}");

            DB::statement("GRANT CONNECT ON DATABASE {$database} TO {$quoted}");
            DB::statement("GRANT USAGE ON SCHEMA public TO {$quoted}");
        }

        $targets = collect(self::GRANTED_BY_DEFAULT)
            ->map(fn (string $key): string => $this->quoteIdentifier($this->roleName($key)))
            ->implode(', ');

        // Bereits bestehende Objekte. Diese Migration laeuft zeitlich nach den
        // Tabellen des Templates, die Default-Privileges unten greifen aber nur
        // fuer kuenftige — ohne diese zwei Zeilen haette die Anwendung auf
        // `users`, `cache` und `jobs` keinen Zugriff.
        DB::statement("GRANT SELECT, INSERT, UPDATE, DELETE ON ALL TABLES IN SCHEMA public TO {$targets}");
        DB::statement("GRANT USAGE, SELECT ON ALL SEQUENCES IN SCHEMA public TO {$targets}");

        // Kuenftige Objekte. Ohne das braeche jede neue Migration die Anwendung
        // zur Laufzeit — die Tabelle existiert, aber niemand darf sie lesen.
        DB::statement("ALTER DEFAULT PRIVILEGES FOR ROLE {$owner} IN SCHEMA public GRANT SELECT, INSERT, UPDATE, DELETE ON TABLES TO {$targets}");
        DB::statement("ALTER DEFAULT PRIVILEGES FOR ROLE {$owner} IN SCHEMA public GRANT USAGE, SELECT ON SEQUENCES TO {$targets}");
    }

    public function down(): void
    {
        if (! $this->onPostgres()) {
            return;
        }

        $database = $this->quoteIdentifier(DB::connection()->getDatabaseName());
        $owner = $this->quoteIdentifier((string) DB::connection()->getConfig('username'));

        $targets = collect(self::GRANTED_BY_DEFAULT)
            ->map(fn (string $key): string => $this->quoteIdentifier($this->roleName($key)))
            ->implode(', ');

        DB::statement("ALTER DEFAULT PRIVILEGES FOR ROLE {$owner} IN SCHEMA public REVOKE ALL ON TABLES FROM {$targets}");
        DB::statement("ALTER DEFAULT PRIVILEGES FOR ROLE {$owner} IN SCHEMA public REVOKE ALL ON SEQUENCES FROM {$targets}");

        DB::statement("GRANT CONNECT ON DATABASE {$database} TO PUBLIC");
        DB::statement('GRANT USAGE ON SCHEMA public TO PUBLIC');

        foreach (self::ALL_ROLES as $key) {
            $role = $this->roleName($key);

            if (! $this->roleExists($role)) {
                continue;
            }

            $quoted = $this->quoteIdentifier($role);

            DB::statement("REVOKE ALL ON ALL TABLES IN SCHEMA public FROM {$quoted}");
            DB::statement("REVOKE ALL ON ALL SEQUENCES IN SCHEMA public FROM {$quoted}");
            DB::statement("REVOKE USAGE ON SCHEMA public FROM {$quoted}");
            DB::statement("REVOKE CONNECT ON DATABASE {$database} FROM {$quoted}");
            DB::statement("DROP OWNED BY {$quoted}");
            DB::statement("DROP ROLE {$quoted}");
        }
    }

    /**
     * Rollen, Grants und RLS gibt es nur in PostgreSQL (ADR-001/036).
     *
     * Die uebrigen Testsuiten laufen derzeit gegen SQLite — dort ist diese
     * Migration gegenstandslos und wuerde sonst den ganzen Lauf abbrechen.
     * Die Architektur-Suite prueft die Rollen gegen ein echtes PostgreSQL.
     */
    private function onPostgres(): bool
    {
        return DB::connection()->getDriverName() === 'pgsql';
    }

    private function roleExists(string $role): bool
    {
        return DB::selectOne('select 1 from pg_roles where rolname = ?', [$role]) !== null;
    }

    /**
     * Rollenname aus der Verbindungskonfiguration — damit Migration und
     * `config/database.php` nicht auseinanderlaufen koennen.
     */
    private function roleName(string $key): string
    {
        $connection = $key === 'staff' ? 'pgsql' : "pgsql_{$key}";
        $name = (string) config("database.connections.{$connection}.username");

        if ($name === '' || $name === (string) DB::connection()->getConfig('username')) {
            throw new RuntimeException(
                "Rolle fuer '{$key}' ist nicht konfiguriert oder identisch mit dem Eigentuemer. ".
                'DB_USERNAME_'.strtoupper($key).' in der .env setzen (ADR-036).'
            );
        }

        return $name;
    }

    private function rolePassword(string $key): string
    {
        $connection = $key === 'staff' ? 'pgsql' : "pgsql_{$key}";

        return (string) config("database.connections.{$connection}.password");
    }

    /**
     * Postgres nimmt fuer Rollen- und Datenbanknamen keine Bind-Parameter.
     */
    private function quoteIdentifier(string $identifier): string
    {
        if (preg_match('/^[a-z_][a-z0-9_]*$/', $identifier) !== 1) {
            throw new InvalidArgumentException("Unzulaessiger Bezeichner: {$identifier}");
        }

        return '"'.$identifier.'"';
    }
};
