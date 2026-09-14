<?php

declare(strict_types=1);

namespace App\Support\DataTable;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

/**
 * Die gemeinsame Mechanik aller Listenansichten: Suche, Sortierung,
 * Seitenaufteilung.
 *
 * Jede Liste im ERP soll sich gleich verhalten — Spalten und Datensatz
 * unterscheiden sich, das Verhalten nicht. Deshalb liegt es hier und nicht in
 * jedem Controller neu.
 *
 * **Serverseitig, nicht im Browser.** Der Adressstamm hat rund 50.000 Personen
 * (HOSTING.md); alles zu laden und im Frontend zu filtern waere bei der ersten
 * echten Liste vorbei.
 *
 * **Sortierung nur ueber eine Freigabeliste.** Der Spaltenname kommt aus der
 * URL und landet in einer `ORDER BY`-Klausel — ohne Pruefung waere das eine
 * offene Tuer, und zwar eine, die auch Spalten aus fremden Tabellen erreicht.
 */
final class DataTable
{
    private const PER_PAGE = 25;

    /**
     * @param  Builder<covariant \Illuminate\Database\Eloquent\Model>  $query
     * @param  array<string, string>  $sortable  Spaltenschluessel => Ausdruck fuer ORDER BY
     * @param  list<string>  $searchable  Ausdruecke fuer die Volltextsuche
     * @param  callable(Model): array<string, mixed>  $map
     * @return array{rows: list<array<string, mixed>>, meta: array<string, mixed>}
     */
    public static function make(
        Builder $query,
        Request $request,
        array $sortable,
        array $searchable,
        callable $map,
        string $defaultSort,
    ): array {
        $search = trim((string) $request->string('suche'));
        $sort = self::resolveSort($request, $sortable, $defaultSort);
        $direction = $request->string('richtung')->lower()->toString() === 'desc' ? 'desc' : 'asc';

        if ($search !== '' && $searchable !== []) {
            $query->where(function (Builder $q) use ($searchable, $search): void {
                foreach ($searchable as $expression) {
                    $q->orWhereRaw("{$expression} ILIKE ?", ['%'.$search.'%']);
                }
            });
        }

        $paginator = $query
            ->orderByRaw($sortable[$sort].' '.$direction)
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        return [
            'rows' => array_map($map, $paginator->items()),
            'meta' => self::meta($paginator, $sort, $direction, $search),
        ];
    }

    /**
     * Nur freigegebene Spalten. Alles andere faellt auf die Vorgabe zurueck —
     * still, denn ein Fehler waere hier nur ein Hinweis darauf, was es sonst
     * noch gibt.
     *
     * @param  array<string, string>  $sortable
     */
    private static function resolveSort(Request $request, array $sortable, string $default): string
    {
        $requested = $request->string('sortierung')->toString();

        return array_key_exists($requested, $sortable) ? $requested : $default;
    }

    /**
     * @param  LengthAwarePaginator<int, Model>  $paginator
     * @return array<string, mixed>
     */
    private static function meta(
        LengthAwarePaginator $paginator,
        string $sort,
        string $direction,
        string $search,
    ): array {
        return [
            'sort' => $sort,
            'direction' => $direction,
            'search' => $search,
            'page' => $paginator->currentPage(),
            'lastPage' => $paginator->lastPage(),
            'perPage' => $paginator->perPage(),
            'total' => $paginator->total(),
            'from' => $paginator->firstItem(),
            'to' => $paginator->lastItem(),
        ];
    }
}
