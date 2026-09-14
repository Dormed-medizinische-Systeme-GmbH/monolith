<?php

declare(strict_types=1);

use App\Modules\Core\Models\User;
use Database\Seeders\RoleSeeder;

/*
| Diese Datei existiert wegen eines Fundes vom 2026-09-14: ein inaktiver
| Mitarbeiter konnte sich anmelden. `IDENTITY_RBAC.md` sagt „inaktiv ⇒ kein
| Login", Fortifys Standardpfad kennt die Spalte aber nicht — er prueft nur
| Mailadresse und Passwort.
*/

beforeEach(function (): void {
    (new RoleSeeder)->run();
});

function loginAt(object $test, string $email, string $password = 'password'): object
{
    return $test->post('http://'.config('domains.erp').'/login', [
        'email' => $email,
        'password' => $password,
    ]);
}

test('ein aktiver Mitarbeiter kommt durch', function (): void {
    $user = User::factory()->create(['email' => 'aktiv@dormed.test']);

    loginAt($this, 'aktiv@dormed.test')->assertRedirect();

    $this->assertAuthenticatedAs($user, 'staff');
});

test('ein inaktiver Mitarbeiter kommt nicht durch', function (): void {
    User::factory()->inactive()->create(['email' => 'inaktiv@dormed.test']);

    loginAt($this, 'inaktiv@dormed.test')->assertSessionHasErrors('email');

    $this->assertGuest('staff');
});

test('ein Mitarbeiter ohne Passwort kommt nicht durch', function (): void {
    // Der spaetere SSO-Fall (D-029): `password` ist nullable. Ein solcher
    // Datensatz darf kein Passwort-Login zulassen — mit keinem Passwort.
    User::factory()->create(['email' => 'sso@dormed.test', 'password' => null]);

    loginAt($this, 'sso@dormed.test', 'irgendetwas')->assertSessionHasErrors('email');

    $this->assertGuest('staff');
});

test('ein geloeschter Mitarbeiter kommt nicht durch', function (): void {
    User::factory()->create(['email' => 'weg@dormed.test'])->delete();

    loginAt($this, 'weg@dormed.test')->assertSessionHasErrors('email');

    $this->assertGuest('staff');
});

test('die Anmeldung haelt den Zeitpunkt fest', function (): void {
    $user = User::factory()->create(['email' => 'stempel@dormed.test', 'last_login_at' => null]);

    loginAt($this, 'stempel@dormed.test');

    expect($user->refresh()->last_login_at)->not->toBeNull();
});

test('die Fehlermeldung unterscheidet nicht zwischen gesperrt und falschem Passwort', function (): void {
    // Sonst liesse sich aus der Antwort ableiten, dass es die Mailadresse gibt.
    User::factory()->inactive()->create(['email' => 'gesperrt@dormed.test']);

    $gesperrt = loginAt($this, 'gesperrt@dormed.test')->assertSessionHasErrors('email');
    $falsch = loginAt($this, 'gesperrt@dormed.test', 'falsch')->assertSessionHasErrors('email');

    expect(session('errors')->get('email'))->toBe($gesperrt->getSession()->get('errors')->get('email'));
    expect($falsch->getSession()->get('errors')->get('email'))
        ->toBe($gesperrt->getSession()->get('errors')->get('email'));
});

test('es gibt keine Selbstbedienung fuer Mitarbeiter', function (string $path): void {
    /*
     * D-032: fuer Mitarbeiter ist nur der Login vorgesehen. Keine
     * Registrierung, kein Self-Service-Passwort-Reset, keine
     * E-Mail-Verifizierung.
     *
     * Ein Selbstregistrierungsformular waere ohnehin an `users.role_id`
     * gescheitert — NOT NULL nach D-124, und niemand kann sich selbst eine
     * Abteilung geben. Das Passwort setzt ein Administrator zurueck, bis
     * Entra-SSO uebernimmt (D-029).
     *
     * Der Test steht hier, damit die Routen nicht durch ein wiederbelebtes
     * Fortify-Feature zurueckkehren, ohne dass es jemand bemerkt.
     */
    $this->get('http://'.config('domains.erp').$path)->assertNotFound();
    $this->post('http://'.config('domains.erp').$path)->assertNotFound();
})->with(['/register', '/forgot-password', '/reset-password', '/email/verify']);

test('der Kunden-Passwort-Reset bleibt davon unberuehrt', function (): void {
    // Kunden brauchen ihn zwingend — sie haben keinen Administrator im Haus
    // (ADR-043). Der Broker steht in config/auth.php mit eigener Token-Tabelle.
    expect(config('auth.passwords.customers.table'))->toBe('customer_password_reset_tokens');
});
