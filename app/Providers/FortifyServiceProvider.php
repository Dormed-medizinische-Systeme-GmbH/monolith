<?php

namespace App\Providers;

use App\Http\Responses\LoginResponse;
use App\Modules\Core\Models\Employee;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        /*
         * In boot(), nicht in register(): Fortifys eigener Provider bindet
         * `LoginResponse` in seinem register() als Singleton und ueberschriebe
         * eine frueher gesetzte Bindung. boot() laeuft nach allen register().
         *
         * Warum ueberhaupt: Fortifys Standardantwort folgt
         * `redirect()->intended()` blind. Bei vier Zugriffspunkten auf einer
         * geteilten Session (ADR-037) kann das gemerkte Ziel auf einem anderen
         * Hostnamen liegen — dann landet eine Mitarbeiter-Anmeldung im Shop,
         * und aus dem Inertia-XHR heraus als CORS-Fehler.
         */
        $this->app->singleton(LoginResponseContract::class, LoginResponse::class);

        $this->configureActions();
        $this->configureViews();
        $this->configureRateLimiting();
    }

    /**
     * Configure Fortify actions.
     */
    private function configureActions(): void
    {

        /*
         * Inaktive Mitarbeiter kommen nicht durch (IDENTITY_RBAC.md: „inaktiv
         * ⇒ kein Login"). Fortifys Standardpfad prueft das NICHT — er kennt nur
         * Mailadresse und Passwort.
         *
         * `null` statt `false` zurueckgeben: damit faellt Fortify auf die
         * uebliche Fehlermeldung zurueck, statt zwischen „falsches Passwort"
         * und „gesperrt" zu unterscheiden. Wer ein Konto sperrt, will nicht,
         * dass ein Angreifer daraus Gueltigkeit der Mailadresse ableitet.
         */
        Fortify::authenticateUsing(function (Request $request): ?Employee {
            $user = Employee::query()
                ->where('email', $request->string('email')->toString())
                ->first();

            if (! $user || ! $user->is_active || $user->password === null) {
                return null;
            }

            if (! Hash::check((string) $request->string('password'), $user->password)) {
                return null;
            }

            $user->forceFill(['last_login_at' => now()])->save();

            return $user;
        });
    }

    /**
     * Configure Fortify views.
     */
    private function configureViews(): void
    {
        Fortify::loginView(fn (Request $request) => Inertia::render('erp/Login', [
            'status' => $request->session()->get('status'),
        ]));

        Fortify::twoFactorChallengeView(fn () => Inertia::render('auth/TwoFactorChallenge'));

        Fortify::confirmPasswordView(fn () => Inertia::render('auth/ConfirmPassword'));
    }

    /**
     * Configure rate limiting.
     */
    private function configureRateLimiting(): void
    {
        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });
    }
}
