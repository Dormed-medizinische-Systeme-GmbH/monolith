<?php

declare(strict_types=1);

use App\Modules\Core\Models\Role;
use App\Modules\Core\Models\Site;
use App\Modules\Core\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function (): void {
    (new RoleSeeder)->run();

    $this->rolle = Role::query()->where('key', 'backoffice')->firstOrFail();
    $this->ich = User::factory()->create();
});

function erp(string $pfad = ''): string
{
    return 'http://'.config('domains.erp').'/mitarbeiter'.$pfad;
}

test('die Liste verlangt eine Anmeldung', function (): void {
    $this->get(erp())->assertRedirect('http://'.config('domains.erp').'/login');
});

test('die Liste zeigt Name, Rolle und Zugangszustand', function (): void {
    User::factory()->create([
        'first_name' => 'Tim',
        'last_name' => 'Weber',
        'email' => 't.weber@dormed.de',
        'password' => null,
        'role_id' => $this->rolle->id,
    ]);

    $this->actingAs($this->ich, 'staff')->get(erp().'?suche=Weber')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('erp/employees/Index')
            ->where('rows.0.name', 'Tim Weber')
            ->where('rows.0.role', 'Backoffice')
            // Ohne Passwort ist der Zugang angelegt, aber nicht nutzbar (D-029).
            ->where('rows.0.hasPassword', false)
            ->where('rows.0.lastLoginAt', null)
        );
});

test('ein Mitarbeiter laesst sich anlegen', function (): void {
    $this->actingAs($this->ich, 'staff')
        ->post(erp(), [
            'first_name' => 'Marlene',
            'last_name' => 'Krause',
            'email' => 'm.krause@dormed.de',
            'password' => 'geheimes-passwort',
            'role_id' => $this->rolle->id,
            'is_active' => true,
            'is_admin' => false,
        ])
        ->assertRedirect();

    $angelegt = User::query()->where('email', 'm.krause@dormed.de')->firstOrFail();

    expect($angelegt->name)->toBe('Marlene Krause');
    expect($angelegt->is_active)->toBeTrue();
    expect(Hash::check('geheimes-passwort', $angelegt->password))->toBeTrue();
});

test('ohne Passwort angelegt bleibt das Feld leer statt leerer Zeichenkette', function (): void {
    /*
     * `null` heisst „kein Passwortlogin" — der Normalfall, sobald Entra-SSO
     * uebernimmt (D-029). `''` hiesse dagegen ein gehashtes Passwort, das
     * niemand kennt, und der Unterschied waere in der Liste nicht sichtbar.
     */
    $this->actingAs($this->ich, 'staff')
        ->post(erp(), [
            'first_name' => 'Tim',
            'last_name' => 'Weber',
            'email' => 't.weber@dormed.de',
            'password' => '',
            'role_id' => $this->rolle->id,
            'is_active' => true,
            'is_admin' => false,
        ])
        ->assertRedirect();

    expect(User::query()->where('email', 't.weber@dormed.de')->value('password'))->toBeNull();
});

test('eine doppelte Mailadresse wird abgewiesen', function (): void {
    $this->actingAs($this->ich, 'staff')
        ->post(erp(), [
            'first_name' => 'Zweiter',
            'last_name' => 'Zugang',
            'email' => $this->ich->email,
            'role_id' => $this->rolle->id,
            'is_active' => true,
            'is_admin' => false,
        ])
        ->assertSessionHasErrors('email');
});

test('eine stillgelegte Rolle laesst sich nicht zuweisen', function (): void {
    // Sonst entstuende ein Mitarbeiter ohne Rechte, und niemand saehe warum.
    $this->rolle->update(['is_active' => false]);

    $this->actingAs($this->ich, 'staff')
        ->post(erp(), [
            'first_name' => 'Ohne',
            'last_name' => 'Rechte',
            'email' => 'ohne@dormed.de',
            'role_id' => $this->rolle->id,
            'is_active' => true,
            'is_admin' => false,
        ])
        ->assertSessionHasErrors('role_id');
});

test('ein leeres Passwort beim Bearbeiten behaelt das bestehende', function (): void {
    $kollege = User::factory()->create(['password' => Hash::make('altes-passwort')]);

    $this->actingAs($this->ich, 'staff')
        ->patch(erp('/'.$kollege->id), [
            'first_name' => $kollege->first_name,
            'last_name' => 'Neuername',
            'email' => $kollege->email,
            'password' => '',
            'role_id' => $kollege->role_id,
            'is_active' => true,
            'is_admin' => false,
        ])
        ->assertRedirect();

    $kollege->refresh();

    expect($kollege->last_name)->toBe('Neuername');
    expect(Hash::check('altes-passwort', $kollege->password))->toBeTrue();
});

test('der eigene Zugang laesst sich nicht stilllegen', function (): void {
    /*
     * Ein inaktiver Mitarbeiter kommt nicht mehr herein, und solange es kein
     * Berechtigungssystem gibt, ist nicht gesagt, dass jemand anderes
     * hereinkaeme. Das ist ein Riegel gegen das Aussperren, keine
     * Abteilungsregel.
     */
    $this->actingAs($this->ich, 'staff')
        ->patch(erp('/'.$this->ich->id), [
            'first_name' => $this->ich->first_name,
            'last_name' => $this->ich->last_name,
            'email' => $this->ich->email,
            'role_id' => $this->ich->role_id,
            'is_active' => false,
            'is_admin' => false,
        ])
        ->assertSessionHasErrors('is_active');

    expect($this->ich->fresh()->is_active)->toBeTrue();
});

test('ein Mitarbeiter wird ausgeblendet, nicht entfernt', function (): void {
    // SoftDeletes (D-018): `TracksBlame` anderer Tabellen zeigt weiter auf ihn.
    $kollege = User::factory()->create();

    $this->actingAs($this->ich, 'staff')
        ->delete(erp('/'.$kollege->id))
        ->assertRedirect();

    expect(User::query()->find($kollege->id))->toBeNull();
    expect(User::withTrashed()->find($kollege->id))->not->toBeNull();
});

test('der eigene Zugang laesst sich nicht loeschen', function (): void {
    $this->actingAs($this->ich, 'staff')
        ->delete(erp('/'.$this->ich->id))
        ->assertRedirect();

    expect(User::query()->find($this->ich->id))->not->toBeNull();
});

test('das Bearbeitungsformular ist vorbelegt', function (): void {
    $kollege = User::factory()->create(['role_id' => $this->rolle->id]);

    $this->actingAs($this->ich, 'staff')
        ->get(erp('/'.$kollege->id.'/bearbeiten'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('erp/employees/Form')
            ->where('employee.email', $kollege->email)
            ->where('employee.role.id', $this->rolle->id)
            // Nur aktive Rollen stehen zur Auswahl.
            ->has('roles', 5)
        );
});

test('die Detailansicht trennt die drei Anmeldewege', function (): void {
    $kollege = User::factory()->withTwoFactor()->create(['last_login_at' => now()]);

    $this->actingAs($this->ich, 'staff')
        ->get(erp('/'.$kollege->id))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('erp/employees/Show')
            ->where('employee.anmeldung.hasPassword', true)
            ->whereNot('employee.anmeldung.twoFactorConfirmedAt', null)
            // Bis SSO uebernimmt, bleibt die Verknuepfung leer (D-029).
            ->where('employee.anmeldung.entraOid', null)
        );
});

test('das Foto kommt aus dem Object Storage und ist nicht ueber die Maske setzbar', function (): void {
    /*
     * `photo_path` ist nicht `$fillable`: ein untergeschobenes Formularfeld
     * kann es nicht setzen. Gepflegt wird es aus dem Seed, spaeter aus einem
     * eigenen Vorgang mit eigener Ability.
     */
    $kollege = User::factory()->create();
    $kollege->photo_path = 'employees/A.Draheim.jpg';
    $kollege->save();

    $this->actingAs($this->ich, 'staff')
        ->get(erp('/'.$kollege->id))
        ->assertInertia(fn (Assert $page) => $page
            // Die Adresse baut `Storage::url()` aus `AWS_URL` — die Anwendung
            // steht nicht im Abrufweg (ADR-045).
            ->where('employee.photoUrl', Storage::disk('s3')->url('employees/A.Draheim.jpg'))
        );

    $this->actingAs($this->ich, 'staff')
        ->patch(erp('/'.$kollege->id), [
            'first_name' => $kollege->first_name,
            'last_name' => $kollege->last_name,
            'email' => $kollege->email,
            'role_id' => $kollege->role_id,
            'is_active' => true,
            'is_admin' => false,
            'photo_path' => 'employees/jemand-anderes.jpg',
        ])
        ->assertRedirect();

    expect($kollege->fresh()->photo_path)->toBe('employees/A.Draheim.jpg');
});

test('ein Mitarbeiter laesst sich einem Dormed-Standort zuordnen', function (): void {
    $standort = Site::query()->create(['name' => 'Buchholz', 'city' => 'Buchholz']);

    $this->actingAs($this->ich, 'staff')
        ->patch(erp('/'.$this->ich->id), [
            'first_name' => $this->ich->first_name,
            'last_name' => $this->ich->last_name,
            'email' => $this->ich->email,
            'role_id' => $this->ich->role_id,
            'site_id' => $standort->id,
            'is_active' => true,
            'is_admin' => false,
        ])
        ->assertRedirect();

    expect($this->ich->fresh()->site_id)->toBe($standort->id);
});

test('kein Standort ist ein gueltiger Zustand', function (): void {
    // Aussendienst oder noch nicht entschieden — anders als die Rolle, die
    // NOT NULL ist (D-124).
    $this->actingAs($this->ich, 'staff')
        ->patch(erp('/'.$this->ich->id), [
            'first_name' => $this->ich->first_name,
            'last_name' => $this->ich->last_name,
            'email' => $this->ich->email,
            'role_id' => $this->ich->role_id,
            'site_id' => '',
            'is_active' => true,
            'is_admin' => false,
        ])
        ->assertRedirect()
        ->assertSessionHasNoErrors();

    expect($this->ich->fresh()->site_id)->toBeNull();
});

test('ein stillgelegter Standort laesst sich nicht zuweisen', function (): void {
    $standort = Site::query()->create(['name' => 'Aufgegeben', 'is_active' => false]);

    $this->actingAs($this->ich, 'staff')
        ->patch(erp('/'.$this->ich->id), [
            'first_name' => $this->ich->first_name,
            'last_name' => $this->ich->last_name,
            'email' => $this->ich->email,
            'role_id' => $this->ich->role_id,
            'site_id' => $standort->id,
            'is_active' => true,
            'is_admin' => false,
        ])
        ->assertSessionHasErrors('site_id');
});
