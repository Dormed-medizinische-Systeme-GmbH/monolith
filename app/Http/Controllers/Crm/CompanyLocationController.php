<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Crm\Concerns\SyncsAddress;
use App\Http\Requests\Crm\StoreLocationRequest;
use App\Http\Requests\Crm\UpdateLocationRequest;
use App\Modules\Crm\Models\Company;
use App\Modules\Crm\Models\Location;
use Illuminate\Http\RedirectResponse;

/**
 * Locations are managed from the company detail page. Authorization piggybacks
 * on the CompanyPolicy: managing a company's locations means updating it.
 */
class CompanyLocationController extends Controller
{
    use SyncsAddress;

    public function store(StoreLocationRequest $request, Company $company): RedirectResponse
    {
        $this->authorize('update', $company);

        $location = $company->locations()->create($request->safe()->only(['name', 'notes']));
        $this->syncAddress($location, $request->validated('address'));

        return redirect()
            ->route('crm.companies.show', $company)
            ->with('status', 'location-created');
    }

    public function update(UpdateLocationRequest $request, Location $location): RedirectResponse
    {
        $this->authorize('update', $location->company);

        $location->update($request->safe()->only(['name', 'notes']));
        $this->syncAddress($location, $request->validated('address'));

        return redirect()
            ->route('crm.companies.show', $location->company)
            ->with('status', 'location-updated');
    }

    public function destroy(Location $location): RedirectResponse
    {
        $this->authorize('update', $location->company);

        $company = $location->company;
        $location->delete();

        return redirect()
            ->route('crm.companies.show', $company)
            ->with('status', 'location-deleted');
    }
}
