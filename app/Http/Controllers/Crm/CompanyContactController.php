<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Http\Requests\Crm\StoreCompanyContactRequest;
use App\Modules\Crm\Models\Company;
use App\Modules\Crm\Models\CompanyContact;
use Illuminate\Http\RedirectResponse;

/**
 * Links an existing person to a company as a contact. Managed from the company
 * detail page; authorization piggybacks on CompanyPolicy@update.
 */
class CompanyContactController extends Controller
{
    public function store(StoreCompanyContactRequest $request, Company $company): RedirectResponse
    {
        $this->authorize('update', $company);

        $company->contacts()->create([
            'person_id' => $request->integer('person_id'),
            'role' => $request->input('role'),
            'is_primary' => $request->boolean('is_primary'),
        ]);

        return redirect()
            ->route('crm.companies.show', $company)
            ->with('status', 'contact-added');
    }

    public function destroy(CompanyContact $companyContact): RedirectResponse
    {
        $this->authorize('update', $companyContact->company);

        $company = $companyContact->company;
        $companyContact->delete();

        return redirect()
            ->route('crm.companies.show', $company)
            ->with('status', 'contact-removed');
    }
}
