<?php

declare(strict_types=1);

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Modules\Crm\Models\Company;
use App\Modules\Crm\Queries\CompanyList;
use App\Modules\Crm\Queries\CompanyProfile;
use App\Support\DataTable\DataTable;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Firmen im ERP — vorerst nur Ansicht.
 *
 * Die Liste fuehrt Firmen, nicht Personen. Ansprechpartner erscheinen erst in
 * der Detailansicht, wo eine Firma den Rahmen bildet und ihre Stammdaten
 * danebenstehen.
 */
final class CompanyController extends Controller
{
    public function index(Request $request): Response
    {
        $table = DataTable::make(
            query: CompanyList::query(),
            request: $request,
            sortable: CompanyList::SORTABLE,
            searchable: CompanyList::SEARCHABLE,
            map: CompanyList::row(...),
            defaultSort: 'name',
        );

        return Inertia::render('erp/companies/Index', $table);
    }

    public function show(Company $company): Response
    {
        return Inertia::render('erp/companies/Show', [
            'company' => CompanyProfile::for($company),
        ]);
    }
}
