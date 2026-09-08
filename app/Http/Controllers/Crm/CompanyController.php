<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Crm\Concerns\SyncsAddress;
use App\Http\Requests\Crm\StoreCompanyRequest;
use App\Http\Requests\Crm\UpdateCompanyRequest;
use App\Modules\Crm\Models\Company;
use App\Modules\Crm\Models\Person;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CompanyController extends Controller
{
    use SyncsAddress;

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Company::class);

        $term = trim((string) $request->query('q', ''));

        $companies = Company::query()
            ->with('address')
            ->withCount('contacts')
            ->when($term !== '', fn ($query) => $query->where('name', 'like', "%{$term}%"))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('crm.companies.index', ['companies' => $companies, 'q' => $term]);
    }

    public function create(): View
    {
        $this->authorize('create', Company::class);

        return view('crm.companies.create', ['company' => new Company]);
    }

    public function store(StoreCompanyRequest $request): RedirectResponse
    {
        $this->authorize('create', Company::class);

        $company = Company::create($request->safe()->only(['name', 'legal_name', 'notes']));
        $this->syncAddress($company, $request->validated('address'));

        return redirect()
            ->route('crm.companies.show', $company)
            ->with('status', 'company-created');
    }

    public function show(Company $company): View
    {
        $this->authorize('view', $company);

        $company->load(['address', 'locations.address', 'contacts.person']);

        $assignablePeople = Person::query()
            ->whereNotIn('id', $company->contacts->pluck('person_id'))
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        return view('crm.companies.show', [
            'company' => $company,
            'assignablePeople' => $assignablePeople,
        ]);
    }

    public function edit(Company $company): View
    {
        $this->authorize('update', $company);

        $company->load('address');

        return view('crm.companies.edit', ['company' => $company]);
    }

    public function update(UpdateCompanyRequest $request, Company $company): RedirectResponse
    {
        $this->authorize('update', $company);

        $company->update($request->safe()->only(['name', 'legal_name', 'notes']));
        $this->syncAddress($company, $request->validated('address'));

        return redirect()
            ->route('crm.companies.show', $company)
            ->with('status', 'company-updated');
    }

    public function destroy(Company $company): RedirectResponse
    {
        $this->authorize('delete', $company);

        $company->delete();

        return redirect()
            ->route('crm.companies.index')
            ->with('status', 'company-deleted');
    }
}
