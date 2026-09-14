<?php

use Illuminate\Support\Str;
use Pdo\Mysql;

/*
 * Die vier Postgres-Verbindungen (ADR-036) unterscheiden sich ausschliesslich in
 * den Zugangsdaten — alles andere identisch zu halten ist Absicht, nicht Zufall.
 * Kein Closure IM Array: das wuerde `config:cache` brechen. Hier wird waehrend des
 * Ladens aufgerufen, das Ergebnis ist ein einfaches Array.
 */
$pgsqlConnection = fn (?string $username, ?string $password): array => [
    'driver' => 'pgsql',
    'url' => env('DB_URL'),
    'host' => env('DB_HOST', '127.0.0.1'),
    'port' => env('DB_PORT', '5432'),
    'database' => env('DB_DATABASE', 'laravel'),
    'username' => $username,
    'password' => $password,
    'charset' => env('DB_CHARSET', 'utf8'),
    'prefix' => '',
    'prefix_indexes' => true,
    'search_path' => 'public',
    'sslmode' => env('DB_SSLMODE', 'prefer'),
];

return [

    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the database connections below you wish
    | to use as your default connection for database operations. This is
    | the connection which will be utilized unless another connection
    | is explicitly specified when you execute a query / statement.
    |
    */

    'default' => env('DB_CONNECTION', 'sqlite'),

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    |
    | Below are all of the database connections defined for your application.
    | An example configuration is provided for each database system which
    | is supported by Laravel. You're free to add / remove connections.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Verbindung je Zugriffspunkt (ADR-036)
    |--------------------------------------------------------------------------
    |
    | Steuert, ob die Middleware der Domain-Gruppen die Standardverbindung
    | umschaltet. In Produktion und lokal: ja, das ist die Sicherheitsgrenze.
    |
    | In der Feature-Testsuite: NEIN. Dort oeffnet `RefreshDatabase` eine
    | Transaktion auf der Standardverbindung; eine Umschaltung im Request
    | erzeugt eine ZWEITE Sitzung, deren Sperren das naechste `migrate:fresh`
    | blockieren — der Lauf haengt, statt fehlzuschlagen.
    |
    | Dass die Umschaltung wirkt, prueft `tests/Architecture/` gegen die echten
    | Rollen: dort gibt es kein RefreshDatabase, und die Verbindungen werden
    | ausdruecklich benannt. Dass jede Domain-Gruppe ihre Middleware ueberhaupt
    | traegt, prueft `RouteDomainBindingTest` statisch.
    |
    */

    'switch_by_access_point' => (bool) env('DB_SWITCH_BY_ACCESS_POINT', true),

    'connections' => [

        'sqlite' => [
            'driver' => 'sqlite',
            'url' => env('DB_URL'),
            'database' => env('DB_DATABASE', database_path('database.sqlite')),
            'prefix' => '',
            'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
            'busy_timeout' => null,
            'journal_mode' => null,
            'synchronous' => null,
            'transaction_mode' => 'DEFERRED',
        ],

        'mysql' => [
            'driver' => 'mysql',
            'url' => env('DB_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'laravel'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => env('DB_CHARSET', 'utf8mb4'),
            'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                Mysql::ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],

        'mariadb' => [
            'driver' => 'mariadb',
            'url' => env('DB_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'laravel'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => env('DB_CHARSET', 'utf8mb4'),
            'collation' => env('DB_COLLATION', 'utf8mb4_unicode_ci'),
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                Mysql::ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ],

        /*
         * Vier Rollen auf EINER Datenbank (ADR-036). Die Verbindung folgt dem
         * Zugriffspunkt, nicht dem Datensatz — umgeschaltet wird per Middleware
         * an der Route-Group, nie ueber $connection auf einem Model.
         *
         *   pgsql           dormed_staff     ERP                 (Standard)
         *   pgsql_customer  dormed_customer  Portal + Shop, eingeloggt
         *   pgsql_public    dormed_public    dormed.de, anonym
         *   pgsql_owner     dormed           NUR Migrations
         *
         * Der Standard ist bewusst NICHT der Eigentuemer: Postgres wendet RLS
         * auf den Tabelleneigentuemer nicht an, und zwar still. Eine vergessene
         * Umschaltung faellt damit auf `staff` zurueck — nicht auf Vollzugriff.
         */
        'pgsql' => [
            ...$pgsqlConnection(
                env('DB_USERNAME_STAFF', env('DB_USERNAME', 'root')),
                env('DB_PASSWORD_STAFF', env('DB_PASSWORD', '')),
            ),
        ],

        'pgsql_customer' => [
            ...$pgsqlConnection(
                env('DB_USERNAME_CUSTOMER', env('DB_USERNAME', 'root')),
                env('DB_PASSWORD_CUSTOMER', env('DB_PASSWORD', '')),
            ),
        ],

        'pgsql_public' => [
            ...$pgsqlConnection(
                env('DB_USERNAME_PUBLIC', env('DB_USERNAME', 'root')),
                env('DB_PASSWORD_PUBLIC', env('DB_PASSWORD', '')),
            ),
        ],

        /*
         * Legt das Schema an und besitzt es. Wird ausschliesslich von
         * `php artisan migrate --database=pgsql_owner` benutzt — nie zur Laufzeit.
         */
        'pgsql_owner' => [
            ...$pgsqlConnection(
                env('DB_USERNAME', 'root'),
                env('DB_PASSWORD', ''),
            ),
        ],

        'sqlsrv' => [
            'driver' => 'sqlsrv',
            'url' => env('DB_URL'),
            'host' => env('DB_HOST', 'localhost'),
            'port' => env('DB_PORT', '1433'),
            'database' => env('DB_DATABASE', 'laravel'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => env('DB_CHARSET', 'utf8'),
            'prefix' => '',
            'prefix_indexes' => true,
            // 'encrypt' => env('DB_ENCRYPT', 'yes'),
            // 'trust_server_certificate' => env('DB_TRUST_SERVER_CERTIFICATE', 'false'),
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Migration Repository Table
    |--------------------------------------------------------------------------
    |
    | This table keeps track of all the migrations that have already run for
    | your application. Using this information, we can determine which of
    | the migrations on disk haven't actually been run on the database.
    |
    */

    'migrations' => [
        'table' => 'migrations',
        'update_date_on_publish' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Redis Databases
    |--------------------------------------------------------------------------
    |
    | Redis is an open source, fast, and advanced key-value store that also
    | provides a richer body of commands than a typical key-value system
    | such as Memcached. You may define your connection settings here.
    |
    */

    'redis' => [

        'client' => env('REDIS_CLIENT', 'phpredis'),

        'options' => [
            'cluster' => env('REDIS_CLUSTER', 'redis'),
            'prefix' => env('REDIS_PREFIX', Str::slug((string) env('APP_NAME', 'laravel')).'-database-'),
            'persistent' => env('REDIS_PERSISTENT', false),
        ],

        'default' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_DB', '0'),
            'max_retries' => env('REDIS_MAX_RETRIES', 3),
            'backoff_algorithm' => env('REDIS_BACKOFF_ALGORITHM', 'decorrelated_jitter'),
            'backoff_base' => env('REDIS_BACKOFF_BASE', 100),
            'backoff_cap' => env('REDIS_BACKOFF_CAP', 1000),
        ],

        'cache' => [
            'url' => env('REDIS_URL'),
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'username' => env('REDIS_USERNAME'),
            'password' => env('REDIS_PASSWORD'),
            'port' => env('REDIS_PORT', '6379'),
            'database' => env('REDIS_CACHE_DB', '1'),
            'max_retries' => env('REDIS_MAX_RETRIES', 3),
            'backoff_algorithm' => env('REDIS_BACKOFF_ALGORITHM', 'decorrelated_jitter'),
            'backoff_base' => env('REDIS_BACKOFF_BASE', 100),
            'backoff_cap' => env('REDIS_BACKOFF_CAP', 1000),
        ],

    ],

];
