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
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
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

        return self::melde('success', "{$employee->name} wurde angelegt.")
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

        return self::melde('success', 'Änderungen gespeichert.')
            ->to(route('erp.employees.show', $user));
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        try {
            Employees::delete($user, $request->user());
        } catch (RuntimeException $e) {
            return self::melde('error', $e->getMessage())->back();
        }

        return self::melde('success', "{$user->name} wurde gelöscht.")
            ->to(route('erp.employees.index'));
    }

    /**
     * Eine Rueckmeldung fuer die naechste Seite.
     *
     * `Inertia::flash()` und NICHT `->with(...)`: Inertia v3 fuehrt einen
     * eigenen Flash-Speicher und liest die Laravel-Session dafuer nicht aus.
     * Ueber `->with()` gesetzte Meldungen kaemen im Browser nie an — ohne
     * Fehler, es taete nur nichts.
     */
    private static function melde(string $type, string $message): Redirector
    {
        Inertia::flash('toast', ['type' => $type, 'message' => $message]);

        return redirect();
    }

    /**
     * Nur aktive Standorte zur Auswahl — derselbe Grund wie bei den Rollen.
     *
     * @return list<array<string, string>>
     */
    private static function sites(): array
    {
        return Site::query()
            ->where('is_active', true)
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
