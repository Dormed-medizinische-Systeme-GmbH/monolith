<?php

declare(strict_types=1);

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Http\Requests\Erp\EmployeeRequest;
use App\Modules\Core\Models\Role;
use App\Modules\Core\Models\Site;
use App\Modules\Core\Models\User;
use App\Modules\Core\Queries\UserList;
use App\Modules\Core\Queries\UserProfile;
use App\Modules\Core\Services\Employees;
use App\Support\DataTable\DataTable;
use App\Support\Flash;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;

/**
 * Mitarbeiter im ERP.
 *
 * Die erste Flaeche mit Schreibzugriff. Angelegt wird ausschliesslich von hier
 * — es gibt keine Registrierung und keinen Self-Service (D-032).
 */
final class EmployeeController extends Controller
{
    public function index(Request $request): Response
    {
        $table = DataTable::make(
            query: UserList::query(),
            request: $request,
            sortable: UserList::SORTABLE,
            searchable: UserList::SEARCHABLE,
            map: UserList::row(...),
            defaultSort: 'name',
        );

        return Inertia::render('erp/employees/Index', $table);
    }

    public function show(User $user): Response
    {
        return Inertia::render('erp/employees/Show', [
            'employee' => UserProfile::for($user),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('erp/employees/Form', [
            'employee' => null,
            'roles' => self::roles(),
            'sites' => self::sites(),
        ]);
    }

    public function store(EmployeeRequest $request): RedirectResponse
    {
        $employee = Employees::create($request->validated());

        return Flash::success("{$employee->name} wurde angelegt.")
            ->to(route('erp.employees.show', $employee));
    }

    public function edit(User $user): Response
    {
        return Inertia::render('erp/employees/Form', [
            'employee' => UserProfile::for($user),
            'roles' => self::roles(),
            'sites' => self::sites(),
        ]);
    }

    public function update(EmployeeRequest $request, User $user): RedirectResponse
    {
        try {
            Employees::update($user, $request->validated(), $request->user());
        } catch (RuntimeException $e) {
            return back()->withErrors(['is_active' => $e->getMessage()]);
        }

        return Flash::success('Änderungen gespeichert.')
            ->to(route('erp.employees.show', $user));
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        try {
            Employees::delete($user, $request->user());
        } catch (RuntimeException $e) {
            return Flash::error($e->getMessage())->back();
        }

        return Flash::success("{$user->name} wurde gelöscht.")
            ->to(route('erp.employees.index'));
    }

    /**
     * Alle Standorte. Es gibt kein Stilllegen — ein Standort ist in Betrieb
     * oder er wird geloescht.
     *
     * @return list<array<string, string>>
     */
    private static function sites(): array
    {
        return Site::query()
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (Site $site): array => ['id' => $site->id, 'name' => $site->name])
            ->all();
    }

    /**
     * Nur aktive Rollen zur Auswahl — eine stillgelegte zuzuweisen hiesse,
     * jemanden ohne Rechte anzulegen.
     *
     * @return list<array<string, string>>
     */
    private static function roles(): array
    {
        return Role::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'key'])
            ->map(fn (Role $role): array => [
                'id' => $role->id,
                'name' => $role->name,
                'key' => $role->key,
            ])
            ->all();
    }
}
