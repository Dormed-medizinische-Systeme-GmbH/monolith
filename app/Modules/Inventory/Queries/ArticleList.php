<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Queries;

use App\Modules\Inventory\Models\Article;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;

/**
 * Die Artikelliste des ERP als Lesemodell.
 *
 * Eine Zeile ist ein Artikel. Der einzige Join geht auf die Artikelgruppe und
 * kann die Zeilen nicht vervielfachen — ein Artikel liegt in genau einer
 * Gruppe (D-100). Exemplare und Bestand werden NICHT mitgeholt: das eine
 * vervielfachte die Zeilen, das andere ist eine Summe ueber den Ledger (D-102)
 * und gehoert nicht in eine Katalogliste.
 */
final class ArticleList
{
    /**
     * @var array<string, string>
     */
    public const SORTABLE = [
        'name' => 'articles.name',
        'number' => 'articles.article_number',
        'group' => 'article_groups.name',
        'manufacturer' => 'articles.manufacturer',
        'price' => 'articles.sale_price',
    ];

    /**
     * @var list<string>
     */
    public const SEARCHABLE = [
        'articles.name',
        'articles.article_number',
        'articles.manufacturer',
        'articles.model_name',
        'articles.manufacturer_article_number',
        'articles.ean',
    ];

    /**
     * @return Builder<Article>
     */
    public static function query(): Builder
    {
        return Article::query()
            ->select('articles.*')
            ->addSelect(['article_groups.name as group_name'])
            ->leftJoin('article_groups', function (JoinClause $join): void {
                $join->on('article_groups.id', '=', 'articles.article_group_id')
                    ->whereNull('article_groups.deleted_at');
            });
    }

    /**
     * @return array<string, mixed>
     */
    public static function row(Article $article): array
    {
        return [
            'id' => $article->id,
            'name' => $article->name,
            'articleNumber' => $article->article_number,
            'group' => $article->getAttribute('group_name'),
            'manufacturer' => trim(implode(' ', array_filter([
                $article->manufacturer, $article->model_name,
            ]))) ?: null,
            'price' => Money::format($article->sale_price),
            'unit' => $article->unit,
            'isActive' => $article->is_active,
            'isPublic' => $article->is_public,
            'isOrderable' => $article->is_orderable,
            'isSerialTracked' => $article->is_serial_tracked,
        ];
    }
}
