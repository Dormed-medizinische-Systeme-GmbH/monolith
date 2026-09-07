# Projektkontext: Laravel + Self-Hosted Supabase + Supabase Auth

## Ziel

Wir bauen eine Laravel-Anwendung, bei der Supabase nicht nur als PostgreSQL-Datenbank,
sondern als vollständige Backend-Infrastruktur genutzt wird.

Wichtig:
- Supabase wird SELF-HOSTED betrieben.
- Keine Abhängigkeit von Supabase Cloud für die eigentliche Anwendung.
- Supabase Auth ist der zentrale Identity Provider.
- Laravel bleibt das primäre Application Framework.
- Die Supabase-spezifische Komplexität soll möglichst vollständig hinter Laravel-Abstraktionen
  (Guard, Middleware, Services, Facades, Models etc.) verborgen werden.
- Im Application-Code soll sich Auth möglichst wie normales Laravel anfühlen.
- PostgreSQL/RLS soll, wo sinnvoll, als zusätzliche Sicherheits-/Autorisierungsschicht genutzt werden.

---

# 1. Grundarchitektur

Gewünschtes Modell:

                    SELF-HOSTED SUPABASE
                           │
              ┌────────────┼────────────┐
              │            │            │
              ▼            ▼            ▼
          Supabase      PostgreSQL    Storage
            Auth           │
              │            │
              │            └── RLS
              │
              │ JWT / Session
              ▼
          Laravel
              │
       ┌──────┴──────┐
       ▼             ▼
   Employee       Customer
    Domain         Domain
       │             │
       └──────┬──────┘
              ▼
       Application Logic


Supabase ist für Identity/Auth zuständig.

Laravel ist für:
- Application Logic
- Domain Logic
- HTTP/API
- Controllers
- Policies
- Gates
- Middleware
- Validation
- Jobs
- Events
- Services
- Domain Models

zuständig.

---

# 2. Supabase Auth ist die zentrale Identity

Supabase `auth.users` ist NICHT die eigentliche Business-User-Tabelle.

Sie ist die technische Identity-Schicht.

Beispielsweise:

auth.users
---------
id
email
phone
created_at
...

Dort gehören Supabase-spezifische Dinge hin:
- Authentication
- Sessions
- OAuth identities
- Microsoft identities
- SAML identities
- MFA
- Password/Magic Link etc.

Die Business-Domäne soll davon getrennt bleiben.

---

# 3. Employee und Customer NICHT in eine gemeinsame Business-User-Tabelle

Bevorzugtes Modell:

auth.users
    │
    ├───────────────┐
    │               │
    ▼               ▼
employees       customers
    │               │
    │               ├── company_id
    │               ├── ...
    │
    ├── department_id
    ├── role_id
    └── ...


Beispiel:

employees
---------
id
auth_user_id  → auth.users.id
department_id
role_id
...

customers
---------
id
auth_user_id  → auth.users.id
company_id
...


Das ist bewusst getrennt.

Grund:
- Employee und Customer sind unterschiedliche fachliche Entitäten.
- Sie haben unterschiedliche Attribute.
- Sie haben unterschiedliche Berechtigungsmodelle.
- Sie gehören zu unterschiedlichen Domänen.
- Eine riesige `users`-Tabelle mit `type = employee/customer` soll vermieden werden.
- Eine gemeinsame Datenbank bedeutet nicht, dass alle Business-Entitäten in einer Tabelle
  zusammengeführt werden müssen.

`auth.users` ist die gemeinsame technische Identity.
`employees` und `customers` sind getrennte fachliche Identitäten/Profile.

---

# 4. Eine Person kann theoretisch mehrere Rollen/Profile besitzen

Wenn fachlich sinnvoll, soll das Datenmodell nicht zwingend voraussetzen:

type = employee
ODER
type = customer

Stattdessen kann dieselbe `auth.users.id` theoretisch auf einen Employee und einen Customer
zeigen.

Beispiel:

auth.users
    │
    ├── employee
    │
    └── customer

Ob diese Kombination tatsächlich erlaubt wird, ist Business Logic.

Nicht unnötig in die Auth-Schicht einbauen.

---

# 5. Laravel Auth soll Supabase abstrahieren

Ziel:

Der normale Laravel-Code soll möglichst nicht wissen, dass Supabase dahinter steckt.

Beispielsweise weiterhin:

Auth::user();
Auth::check();
Auth::logout();

Middleware:

Route::middleware('auth')->group(...);

Policies:

$this->authorize('update', $resource);

Gates:

Gate::allows(...);

Controller sollen NICHT überall direkten Supabase-Code enthalten.

Vermeiden:

supabase.auth...
supabase.getUser...
JWT manuell auslesen...
Supabase API direkt in jedem Controller...

Diese Dinge gehören in eine zentrale Infrastruktur-/Auth-Schicht.

---

# 6. Geplante Laravel-Abstraktion

Mögliche Komponenten:

SupabaseAuthService
SupabaseGuard
SupabaseUserProvider
SupabaseUser
SupabaseTokenManager
SupabaseSessionManager

Middleware:

auth
auth.supabase
employee
customer

Zielarchitektur:

HTTP Request
    │
    ▼
Supabase Auth Middleware
    │
    ├── Session/JWT erkennen
    ├── JWT validieren
    ├── Supabase User bestimmen
    ├── Domain Identity laden
    └── Laravel Auth Context setzen
             │
             ▼
         Auth::user()
             │
             ▼
       normale Laravel App


Die konkrete Implementierung soll sich möglichst an den Laravel Auth Contracts orientieren,
statt einen komplett eigenen Auth-Mechanismus zu erfinden.

---

# 7. Authentication vs Authorization strikt trennen

Authentication:

"Wer bist du?"

→ Supabase Auth

Authorization:

"Was darfst du?"

→ Laravel Policies/Gates/Roles/Permissions
→ ggf. PostgreSQL RLS als zweite Sicherheitsgrenze

Beispiel:

Supabase:
    user = abc123

Laravel:
    abc123 → Employee
    department = support
    role = manager

oder:

    abc123 → Customer
    company = company_42

Diese Trennung ist wichtig.

---

# 8. Mitarbeiter

Employees sollen ein eigenes Berechtigungsmodell bekommen.

Nicht einfach nur:

is_admin = true

Bevorzugt:

Employee
    │
    ├── Departments
    │
    ├── Roles
    │
    └── Permissions

Beispiel:

Employee
├── Department: Support
├── Department: Sales
└── Roles:
      ├── employee
      └── manager

Permissions könnten z.B. sein:

tickets.read
tickets.write
customers.read
customers.write
users.manage
billing.read
...

Ein Super-/Admin-Status kann zusätzlich existieren, sollte aber nicht das komplette
Autorisierungsmodell ersetzen.

---

# 9. Kunden

Customers haben einen eigenen Domain-/Tenant-Kontext.

Beispiel:

Customer
    │
    ▼
Company
    │
    ├── Customer User A
    ├── Customer User B
    └── Projects

Ein Kunde darf nur Daten seiner eigenen Company/Organisation sehen.

Beispiel:

Customer A
    ↓
Company A
    ↓
Projects A

Customer B
    ↓
Company B
    ↓
Projects B

Ein Zugriff von Customer A auf Daten von Company B muss verhindert werden.

---

# 10. PostgreSQL RLS

Supabase RLS soll nicht blind ignoriert werden.

Wenn möglich, soll RLS als zweite Sicherheitsgrenze genutzt werden.

Beispiel:

User
    ↓
Supabase Auth
    ↓
JWT
    ↓
Request
    ↓
Supabase/Postgres
    ↓
RLS


Beispielhafte Policy:

create policy "Users can access own documents"
on documents
for all
to authenticated
using (
    user_id = auth.uid()
);


Wichtig:

Eine normale privilegierte PostgreSQL-Verbindung aus Laravel kennt den Supabase-User-JWT
nicht automatisch.

Daher gilt:

Laravel → direkte PostgreSQL-Verbindung

ist NICHT automatisch dasselbe wie:

Supabase Client/API → PostgreSQL mit User-JWT → RLS


Wenn RLS anhand des aktuellen Supabase-Users wirken soll, muss der Auth-Kontext/JWT entsprechend
bis zur Supabase-Datenebene weitergegeben werden.

Diese Architekturentscheidung muss bei der konkreten Implementierung berücksichtigt werden.

Nicht einfach behaupten, dass Eloquent über eine normale DB-Verbindung automatisch `auth.uid()`
kennt.

---

# 11. Zwei mögliche Datenzugriffsmodelle

## Modell A

Laravel:

Laravel
   │
   ▼
PostgreSQL
   │
   ▼
Supabase DB

Laravel übernimmt Authorization.

Vorteil:
- sehr einfach
- Eloquent funktioniert natürlich
- klassisches Laravel

Nachteil:
- RLS ist nicht automatisch an den eingeloggten User gekoppelt.


## Modell B

Laravel + Supabase User Context:

Laravel
   │
   ├── Auth::user()
   │
   └── User JWT
          │
          ▼
     Supabase API
          │
          ▼
      PostgreSQL
          │
          ▼
          RLS

Das ist das bevorzugte Modell, wenn Supabase RLS tatsächlich als zusätzliche
Sicherheitsgrenze genutzt werden soll.

---

# 12. Microsoft / Entra ID

Microsoft Login soll über Supabase Auth laufen.

Self-Hosted Supabase unterstützt Microsoft/Azure/Entra als Auth Provider.

Möglicher Flow:

Microsoft Entra ID
       │
       ▼
Supabase Auth
       │
       ▼
auth.users
       │
       ▼
JWT / Session
       │
       ▼
Laravel
       │
       ▼
Auth::user()


Für Mitarbeiter kann Microsoft/Entra verpflichtend sein.

Kunden können beispielsweise:
- Email/Password
- Microsoft
- Google
- andere OAuth Provider
- Magic Link

verwenden.

Authentication bleibt trotzdem ein gemeinsames System.

---

# 13. Enterprise SSO / SAML

Nicht verwechseln:

1. Supabase Cloud Dashboard SSO
2. Supabase Auth SSO für die eigene Anwendung

Das Projekt soll Self-Hosted sein.

Supabase Auth kann in Self-Hosted-Setups Enterprise-Identity-Provider wie Microsoft Entra
über SAML/OIDC einbinden.

Das bedeutet:

Microsoft Entra
    │
    │ SAML/OIDC
    ▼
Self-Hosted Supabase Auth
    │
    ▼
Laravel


Das soll NICHT voraussetzen, dass die eigentliche Anwendung Supabase Cloud verwendet.

---

# 14. Auth Provider sollen von Laravel abstrahiert werden

Die Laravel-Anwendung sollte möglichst keinen Unterschied machen zwischen:

- Email/Password
- Microsoft
- Google
- SAML
- Magic Link
- MFA

Beispielhafte gewünschte API:

Auth::user();

Auth::check();

Auth::logout();

und für Provider ggf.:

Auth::redirectToProvider('microsoft');

oder eine eigene Auth-Service-Abstraktion.

Der konkrete Supabase-Mechanismus bleibt in der Infrastructure Layer.

---

# 15. Darstellungsschicht

Employee und Customer sind auch auf UI-/Routing-Ebene getrennt möglich.

Beispiel:

/admin/...
/employee/...
/portal/...
/customer/...

Middleware:

auth
employee
customer

Beispiel:

Route::middleware(['auth', 'employee'])
    ->prefix('admin')
    ->group(...);

Route::middleware(['auth', 'customer'])
    ->prefix('portal')
    ->group(...);


Das bedeutet NICHT, dass zwei Auth-Systeme existieren.

Es gibt:

EINE technische Identity

aber mehrere fachliche Personas/Domänen.

---

# 16. Grundprinzip

Das gesamte System soll diese Verantwortlichkeiten haben:

Supabase Auth
----------------
- Identity
- Login
- Logout
- Sessions
- OAuth
- Microsoft
- SAML
- MFA
- Password reset
- Magic links
- auth.users


Laravel
----------------
- Auth abstraction
- Middleware
- Controllers
- Domain Logic
- Policies
- Gates
- Roles
- Permissions
- Employee domain
- Customer domain
- Business rules


PostgreSQL / Supabase
----------------
- relational data
- constraints
- foreign keys
- functions
- triggers
- RLS
- extensions
- ggf. Realtime


---

# 17. Gewünschtes Entwicklererlebnis

Der Entwickler soll möglichst normales Laravel schreiben.

Beispiel:

class InvoiceController extends Controller
{
    public function update(Invoice $invoice)
    {
        $this->authorize('update', $invoice);

        $invoice->update([
            'status' => 'paid',
        ]);

        return response()->json($invoice);
    }
}


Nicht:

class InvoiceController extends Controller
{
    public function update(...)
    {
        $supabaseUser = ...
        $jwt = ...
        $supabase = ...
        ...
    }
}


Supabase-spezifische Details gehören in:
- Guard
- Middleware
- Services
- Provider
- Repository/Infrastructure Layer
- ggf. Supabase Client Wrapper

Nicht in die Business Logic.

---

# 18. Architekturprinzip

Die Anwendung soll nicht von Supabase-spezifischen APIs abhängig werden, wo es nicht nötig ist.

Ideal:

Application Layer
        │
        ▼
Laravel abstractions
        │
        ▼
Infrastructure
        │
        ├── Supabase Auth
        ├── Supabase API
        └── PostgreSQL


So bleibt theoretisch später ein Wechsel des Identity Providers möglich.

Beispielsweise:

Laravel Application
        │
        ▼
Auth Contract
        │
        ├── Supabase implementation
        ├── anderer OIDC Provider
        └── anderer Identity Provider


Das ist kein kurzfristiges Ziel, aber eine sinnvolle Grenze.

---

# 19. Supabase ist trotzdem zentral

Nicht versuchen, Supabase nur als "Postgres Hosting" zu verwenden.

Das Projekt soll die relevanten Supabase-Funktionen tatsächlich nutzen können:

- Supabase Auth
- OAuth
- Microsoft / Entra
- SAML / Enterprise SSO
- MFA
- Sessions
- PostgreSQL
- RLS
- Storage
- Realtime
- ggf. Edge Functions
- ggf. pgvector
- ggf. weitere Supabase-Dienste

Die Laravel-Abstraktion soll diese Funktionen kapseln, nicht eliminieren.

---

# 20. MCP / Agents

Langfristig soll das System auch Agent-/MCP-fähig sein.

Wichtig:
Der offizielle Appwrite/Supabase Cloud Remote-MCP-Ansatz soll nicht mit Self-Hosted verwechselt
werden.

Für Self-Hosted ist ein eigener Remote-MCP-Service möglich.

Architektur:

AI Client
    │
    │ HTTPS / MCP
    ▼
mcp.example.com
    │
    ▼
separater MCP Service
    │
    ▼
Self-Hosted Supabase API


Der MCP soll NICHT zwingend Bestandteil des eigentlichen Supabase-Stacks sein.

Für Coolify ist ein separater Service sinnvoll.

`uvx` ist primär eine einfache Möglichkeit, einen MCP lokal auf dem Client zu starten.
Für einen dauerhaft erreichbaren Remote-MCP soll ein dedizierter MCP-Service verwendet werden.

---

# 21. Self-Hosted als harte Vorgabe

Die Architektur darf nicht stillschweigend Features voraussetzen, die nur Supabase Cloud
oder Enterprise-Tarife anbieten.

Bei jeder Feature-Entscheidung prüfen:

- Ist das in Self-Hosted verfügbar?
- Welche Supabase-Version wird verwendet?
- Ist es Cloud-only?
- Ist es Enterprise-only?
- Muss es selbst konfiguriert werden?
- Welche Infrastruktur wird benötigt?


Nicht einfach aus Cloud-Dokumentation schließen:
"Supabase kann das, also funktioniert es automatisch Self-Hosted."

---

# 22. Aktueller Denkstand

Die wichtigsten Entscheidungen sind bereits getroffen:

1. Supabase Auth ist der zentrale Identity Provider.
2. Laravel soll Supabase Auth hinter normalen Laravel-Auth-Abstraktionen verstecken.
3. `auth.users` bleibt technische Identity.
4. `employees` und `customers` bleiben getrennte fachliche Tabellen.
5. Eine gemeinsame Datenbank bedeutet nicht eine gemeinsame Business-User-Tabelle.
6. Eine `auth.users.id` kann theoretisch mehrere Domain-Profile referenzieren.
7. Mitarbeiter bekommen ein eigenes Rollen-/Abteilungs-/Permission-Modell.
8. Kunden bekommen einen eigenen Company-/Tenant-Kontext.
9. Microsoft Entra / SSO soll für Self-Hosted Mitarbeiter-Auth nutzbar sein.
10. Supabase RLS soll, wo sinnvoll, als zusätzliche Datenbank-Sicherheitsgrenze verwendet werden.
11. Dabei darf nicht übersehen werden, dass eine direkte privilegierte Laravel-Postgres-Verbindung
    den User-JWT-Kontext nicht automatisch an RLS weitergibt.
12. Laravel bleibt das Application Framework und soll sich für Entwickler wie normales Laravel anfühlen.
13. Supabase-spezifischer Code soll zentral gekapselt werden.
14. Remote MCP/Agent-Infrastruktur soll separat und self-hosted betreibbar sein.
15. Cloud-/Enterprise-only-Annahmen sind zu vermeiden.

---

# 23. Wichtige Korrektur für zukünftige Diskussionen

Nicht behaupten:

"Laravel Eloquent auf Supabase Postgres nutzt automatisch Supabase RLS für den eingeloggten User."

Das ist falsch bzw. unvollständig.

Korrekt:

"RLS kann auf Supabase Postgres genutzt werden, aber bei direktem Laravel-DB-Zugriff muss der
User-/JWT-Kontext explizit so an die Datenbankebene gebracht werden, dass Supabase/Postgres
ihn für RLS auswerten kann."

Ebenso nicht behaupten:

"Supabase hat eine vollständige offizielle Laravel Auth Integration."

Aktuell ist das Ziel eine eigene Laravel-Integration/Abstraktionsschicht über Supabase Auth.

---

# 24. Zielbild in einem Satz

Supabase Auth liefert die Identität, Laravel abstrahiert und verwendet diese Identität wie
native Laravel Authentication, Employee und Customer bleiben getrennte Business-Domänen,
PostgreSQL/RLS bildet eine zusätzliche Sicherheitsgrenze, und die gesamte Infrastruktur bleibt
Self-Hosted.
