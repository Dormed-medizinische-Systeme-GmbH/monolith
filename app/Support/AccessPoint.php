<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Was gerade greift — Zugriffspunkt, Verbindung, Datenbankrolle.
 *
 * Existiert, damit die Architektur aus ADR-033/036/039 im Browser und im Test
 * sichtbar ist, statt nur in den Dokumenten zu stehen. Traegt keine Fachlogik.
 *
 * @phpstan-type AccessPointPayload array{
 *     key: string,
 *     label: string,
 *     host: string,
 *     frontend: string,
 *     connection: string,
 *     role: string,
 * }
 */
final class AccessPoint
{
    /**
     * @return AccessPointPayload
     */
    public static function describe(string $key, string $label, string $frontend): array
    {
        $connection = DB::getDefaultConnection();

        return [
            'key' => $key,
            'label' => $label,
            'host' => (string) config("domains.{$key}"),
            'frontend' => $frontend,
            'connection' => $connection,
            'role' => self::currentRole($connection),
        ];
    }

    /**
     * Die Rolle wird bei der Datenbank erfragt, nicht aus der Konfiguration
     * gelesen — sonst bewiese die Anzeige nur, was in der `.env` steht.
     */
    private static function currentRole(string $connection): string
    {
        try {
            return (string) DB::connection($connection)->scalar('select current_user');
        } catch (Throwable $e) {
            return 'nicht verbunden: '.class_basename($e);
        }
    }
}
