<?php

declare(strict_types=1);

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Modules\Crm\Models\Person;
use App\Support\DataTable\DataTable;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Kontaktuebersicht im ERP — vorerst nur Ansicht.
 *
 * Zeigt Personen aus dem Kundenstamm zusammen mit ihrer Firma UND dem Zustand
 * ihres Portalzugangs. Der letzte Punkt ist der eigentliche Zweck: er macht
 * sichtbar, wie CRM-Kontakt und Anmeldung zusammenhaengen (ADR-037) — eine
 * Person ohne Zugang, eine mit, eine gesperrte.
 */
final class ContactController extends Controller
{
    public function __invoke(Request $request): Response
    {
        /*
         * Joins statt `with()`: nach Firmenname sortieren laesst sich nur, wenn
         * die Spalte in derselben Abfrage liegt. `leftJoin`, weil eine Person
         * theoretisch ohne Firmenzuordnung existieren kann — sie soll dann in
         * der Liste stehen und nicht verschwinden.
         */
        $query = Person::query()
            ->select('people.*')
            /*
             * Als Liste mit `as`, NICHT als `alias => spalte`: die
             * Schluessel-Form von `addSelect()` erwartet Unterabfragen und
             * liefert bei Spaltennamen stillschweigend nichts.
             */
            ->addSelect([
                'companies.name as company_name',
                'company_contacts.role as contact_role',
                'customer_accounts.email as account_email',
                'customer_accounts.is_active as account_active',
            ])
            ->leftJoin('company_contacts', function ($join): void {
                $join->on('company_contacts.person_id', '=', 'people.id')
                    ->whereNull('company_contacts.deleted_at');
            })
            ->leftJoin('companies', function ($join): void {
                $join->on('companies.id', '=', 'company_contacts.company_id')
                    ->whereNull('companies.deleted_at');
            })
            ->leftJoin('customer_accounts', function ($join): void {
                $join->on('customer_accounts.person_id', '=', 'people.id')
                    ->whereNull('customer_accounts.deleted_at');
            });

        $table = DataTable::make(
            query: $query,
            request: $request,
            sortable: [
                'name' => 'people.last_name',
                'company' => 'companies.name',
                'role' => 'company_contacts.role',
                'access' => 'customer_accounts.email',
            ],
            searchable: [
                "people.first_name || ' ' || people.last_name",
                'companies.name',
                'customer_accounts.email',
            ],
            map: fn (Person $person): array => [
                'id' => $person->id,
                'name' => trim("{$person->first_name} {$person->last_name}"),
                'nameSuffix' => $person->name_suffix,
                'company' => $person->getAttribute('company_name'),
                'role' => $person->getAttribute('contact_role'),
                'accountEmail' => $person->getAttribute('account_email'),
                'accountActive' => (bool) $person->getAttribute('account_active'),
            ],
            defaultSort: 'name',
        );

        return Inertia::render('erp/contacts/Index', $table);
    }
}
