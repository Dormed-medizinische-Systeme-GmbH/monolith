<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Enums;

/**
 * Auf welcher Stufe ein benutzerdefiniertes Feld lebt (D-119).
 *
 * `Article`: alle Exemplare tragen denselben Wert — Laenge, Gewicht, Anzahl
 * Kanaele. Eine Erfassung je Stueck waere stumpfe Wiederholung.
 *
 * `Item`: je Einzelstueck verschieden — MAC-Adresse, Ausstattung. Wird beim
 * Wareneingang je Stueck erfasst und haengt am Exemplar.
 */
enum FieldScope: string
{
    case Article = 'article';
    case Item = 'item';

    public function label(): string
    {
        return match ($this) {
            self::Article => 'je Artikel',
            self::Item => 'je Exemplar',
        };
    }
}
