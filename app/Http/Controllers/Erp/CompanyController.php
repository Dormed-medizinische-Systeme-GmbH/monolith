<?php

declare(strict_types=1);

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Http\Requests\Erp\LocationRequest;
use App\Modules\Crm\Models\Company;
use App\Modules\Crm\Models\CompanyContact;
use App\Modules\Crm\Models\Location;
use App\Modules\Crm\Queries\CompanyContactProfile;
use App\Modules\Crm\Queries\CompanyList;
use App\Modules\Crm\Queries\CompanyProfile;
use App\Modules\Crm\Services\Locations;
use App\Support\DataTable\DataTable;
use App\Support\Flash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;

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

    /**
     * Ein Ansprechpartner dieser Firma.
     *
     * `$company` ist nicht nur Zierde: die Route bindet verschachtelt
     * (`scopeBindings`), ein Kontakt einer anderen Firma ist unter diesem Pfad
     * deshalb nicht erreichbar.
     */
    public function contact(Company $company, CompanyContact $contact): Response
    {
        return Inertia::render('erp/companies/contacts/Show', [
            'contact' => CompanyContactProfile::for($contact),
        ]);
    }

    /**
     * Standorte gehoeren in die Firmenakte, nicht in eine eigene Flaeche: ein
     * Kundenstandort kann ohne Firma nicht existieren (`company_id` NOT NULL),
     * und „Hauptstandort" allein sagt nichts — es gibt einen je Praxis.
     *
     * Die Firma kommt deshalb aus der Route und ist nicht uebersteuerbar.
     */
    public function storeLocation(LocationRequest $request, Company $company): RedirectResponse
    {
        $location = Locations::create(
            $company,
            $request->locationAttributes(),
            $request->addressAttributes(),
        );

        return Flash::success("Standort „{$location->name}\" wurde angelegt.")
            ->to(route('erp.companies.show', $company));
    }

    public function updateLocation(
        LocationRequest $request,
        Company $company,
        Location $location,
    ): RedirectResponse {
        try {
            Locations::update($location, $request->locationAttributes(), $request->addressAttributes());
        } catch (RuntimeException $e) {
            return Flash::error($e->getMessage())->back();
        }

        return Flash::success('Änderungen gespeichert.')
            ->to(route('erp.companies.show', $company));
    }

    public function destroyLocation(Company $company, Location $location): RedirectResponse
    {
        try {
            Locations::delete($location);
        } catch (RuntimeException $e) {
            return Flash::error($e->getMessage())->back();
        }

        return Flash::success("Standort „{$location->name}\" wurde gelöscht.")
            ->to(route('erp.companies.show', $company));
    }
}
