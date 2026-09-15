<?php

declare(strict_types=1);

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Http\Requests\Erp\CompanyRequest;
use App\Http\Requests\Erp\LocationRequest;
use App\Modules\Core\Models\User;
use App\Modules\Crm\Models\Company;
use App\Modules\Crm\Models\CompanyContact;
use App\Modules\Crm\Models\Location;
use App\Modules\Crm\Models\MedicalSpecialty;
use App\Modules\Crm\Queries\CompanyContactProfile;
use App\Modules\Crm\Queries\CompanyList;
use App\Modules\Crm\Queries\CompanyProfile;
use App\Modules\Crm\Services\Companies;
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

    public function create(): Response
    {
        return Inertia::render('erp/companies/Form', [
            'company' => null,
            ...self::auswahlfelder(null),
        ]);
    }

    public function store(CompanyRequest $request): RedirectResponse
    {
        $company = Companies::create($request->companyAttributes(), $request->addressAttributes());

        return Flash::success("{$company->name} wurde angelegt.")
            ->to(route('erp.companies.show', $company));
    }

    public function edit(Company $company): Response
    {
        return Inertia::render('erp/companies/Form', [
            'company' => CompanyProfile::for($company),
            ...self::auswahlfelder($company),
        ]);
    }

    public function update(CompanyRequest $request, Company $company): RedirectResponse
    {
        try {
            Companies::update($company, $request->companyAttributes(), $request->addressAttributes());
        } catch (RuntimeException $e) {
            return Flash::error($e->getMessage())->back();
        }

        return Flash::success('Änderungen gespeichert.')
            ->to(route('erp.companies.show', $company));
    }

    public function destroy(Company $company): RedirectResponse
    {
        Companies::delete($company);

        return Flash::success("{$company->name} wurde gelöscht.")
            ->to(route('erp.companies.index'));
    }

    /**
     * Die Auswahllisten der Firmenmaske.
     *
     * Die Zustaendigen kommen je aus IHRER Abteilung — Vertrieb aus `sales`,
     * Service aus `service`. Die Rolle ist die einzige Quelle dafuer, wer wozu
     * gehoert (D-124); eine zweite Liste daneben liefe unweigerlich
     * auseinander. Der bisherige Zustaendige bleibt in der Liste, auch wenn er
     * die Abteilung gewechselt hat — sonst verschwaende er beim naechsten
     * Speichern stillschweigend.
     *
     * Die Zuordnung bleibt informativ, sie ist keine Berechtigung (D-016).
     *
     * @return array<string, mixed>
     */
    private static function auswahlfelder(?Company $company): array
    {
        return [
            'salesEmployees' => self::abteilung('sales', $company?->responsible_sales_id),
            'serviceEmployees' => self::abteilung('service', $company?->responsible_service_id),

            'specialties' => MedicalSpecialty::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn (MedicalSpecialty $fach): array => [
                    'id' => $fach->id,
                    'name' => $fach->name,
                ])
                ->all(),

            // Eine Firma kann nicht ihr eigener Rechnungsempfaenger sein.
            'companies' => Company::query()
                ->when($company !== null, fn ($query) => $query->whereKeyNot($company->getKey()))
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(fn (Company $andere): array => [
                    'id' => $andere->id,
                    'name' => $andere->name,
                ])
                ->all(),
        ];
    }

    /**
     * Die aktiven Mitarbeiter einer Abteilung, plus den bisherigen Zustaendigen.
     *
     * Die Freigabeliste ist dieselbe, gegen die `CompanyRequest` prueft —
     * einmal definiert, damit Maske und Pruefung nicht auseinanderlaufen.
     *
     * @return list<array<string, mixed>>
     */
    private static function abteilung(string $rolle, ?string $bisher): array
    {
        return User::query()
            ->whereIn('id', CompanyRequest::auswaehlbar($rolle, $bisher))
            ->with('role')
            ->orderBy('last_name')
            ->get()
            ->map(fn (User $user): array => [
                'id' => $user->id,
                'name' => $user->name,
                // Kennzeichnet den Sonderfall, statt ihn zu verstecken.
                'foreign' => $user->role?->key !== $rolle || ! $user->is_active,
            ])
            ->all();
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
