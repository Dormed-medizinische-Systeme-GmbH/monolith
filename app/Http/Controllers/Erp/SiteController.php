<?php

declare(strict_types=1);

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Http\Requests\Erp\SiteRequest;
use App\Modules\Core\Models\Site;
use App\Modules\Core\Queries\SiteList;
use App\Modules\Core\Queries\SiteProfile;
use App\Modules\Core\Services\Sites;
use App\Support\DataTable\DataTable;
use App\Support\Flash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;

/**
 * Die eigenen Standorte von Dormed — nicht die der Kunden (D-007).
 */
final class SiteController extends Controller
{
    public function index(Request $request): Response
    {
        $table = DataTable::make(
            query: SiteList::query(),
            request: $request,
            sortable: SiteList::SORTABLE,
            searchable: SiteList::SEARCHABLE,
            map: SiteList::row(...),
            defaultSort: 'name',
        );

        return Inertia::render('erp/sites/Index', $table);
    }

    public function show(Site $site): Response
    {
        return Inertia::render('erp/sites/Show', ['site' => SiteProfile::for($site)]);
    }

    public function create(): Response
    {
        return Inertia::render('erp/sites/Form', ['site' => null]);
    }

    public function store(SiteRequest $request): RedirectResponse
    {
        $site = Sites::create($request->validated());

        return Flash::success("{$site->name} wurde angelegt.")
            ->to(route('erp.sites.show', $site));
    }

    public function edit(Site $site): Response
    {
        return Inertia::render('erp/sites/Form', ['site' => SiteProfile::for($site)]);
    }

    public function update(SiteRequest $request, Site $site): RedirectResponse
    {
        Sites::update($site, $request->validated());

        return Flash::success('Änderungen gespeichert.')->to(route('erp.sites.show', $site));
    }

    public function destroy(Site $site): RedirectResponse
    {
        try {
            Sites::delete($site);
        } catch (RuntimeException $e) {
            return Flash::error($e->getMessage())->back();
        }

        return Flash::success("{$site->name} wurde gelöscht.")->to(route('erp.sites.index'));
    }
}
