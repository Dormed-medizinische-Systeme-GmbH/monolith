<?php

namespace App\Http\Controllers\Crm;

use App\Http\Controllers\Controller;
use App\Http\Requests\Crm\StorePersonRequest;
use App\Http\Requests\Crm\UpdatePersonRequest;
use App\Modules\Crm\Models\Person;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PersonController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Person::class);

        $term = trim((string) $request->query('q', ''));

        $people = Person::query()
            ->when($term !== '', fn ($query) => $query
                ->where('last_name', 'like', "%{$term}%")
                ->orWhere('first_name', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%"))
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->paginate(20)
            ->withQueryString();

        return view('crm.people.index', ['people' => $people, 'q' => $term]);
    }

    public function create(): View
    {
        $this->authorize('create', Person::class);

        return view('crm.people.create', ['person' => new Person]);
    }

    public function store(StorePersonRequest $request): RedirectResponse
    {
        $this->authorize('create', Person::class);

        $person = Person::create($request->validated());

        return redirect()
            ->route('crm.people.show', $person)
            ->with('status', 'person-created');
    }

    public function show(Person $person): View
    {
        $this->authorize('view', $person);

        $person->load('contacts.company');

        return view('crm.people.show', ['person' => $person]);
    }

    public function edit(Person $person): View
    {
        $this->authorize('update', $person);

        return view('crm.people.edit', ['person' => $person]);
    }

    public function update(UpdatePersonRequest $request, Person $person): RedirectResponse
    {
        $this->authorize('update', $person);

        $person->update($request->validated());

        return redirect()
            ->route('crm.people.show', $person)
            ->with('status', 'person-updated');
    }

    public function destroy(Person $person): RedirectResponse
    {
        $this->authorize('delete', $person);

        $person->delete();

        return redirect()
            ->route('crm.people.index')
            ->with('status', 'person-deleted');
    }
}
