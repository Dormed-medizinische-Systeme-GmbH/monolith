<?php

declare(strict_types=1);

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Modules\Inventory\Models\Article;
use App\Modules\Inventory\Queries\ArticleList;
use App\Modules\Inventory\Queries\ArticleProfile;
use App\Support\DataTable\DataTable;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Artikel im ERP — vorerst nur Ansicht.
 *
 * Katalog, kein Bestand: was hier steht, ist der Artikelstamm. Bestand ist die
 * Summe ueber den Bewegungs-Ledger (D-102) und existiert noch nicht.
 */
final class ArticleController extends Controller
{
    public function index(Request $request): Response
    {
        $table = DataTable::make(
            query: ArticleList::query(),
            request: $request,
            sortable: ArticleList::SORTABLE,
            searchable: ArticleList::SEARCHABLE,
            map: ArticleList::row(...),
            defaultSort: 'name',
        );

        return Inertia::render('erp/articles/Index', $table);
    }

    public function show(Article $article): Response
    {
        return Inertia::render('erp/articles/Show', [
            'article' => ArticleProfile::for($article),
        ]);
    }
}
