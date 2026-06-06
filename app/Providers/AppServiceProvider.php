<?php

namespace App\Providers;

use App\Contracts\WhatsAppGateway;
use App\Services\WhatsApp\DeepLinkWhatsApp;
use Carbon\CarbonImmutable;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Gateway de WhatsApp desacoplado (deep link wa.me por defecto).
        $this->app->bind(
            WhatsAppGateway::class,
            DeepLinkWhatsApp::class,
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (app()->isProduction()) {
            URL::forceScheme('https');
        }

        $this->configureDefaults();
        $this->configureAuthorization();
        $this->configureRateLimiting();
    }

    /** Limitadores nombrados para las rutas públicas del cliente. */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('mesa-public', fn (Request $request) => Limit::perMinute(60)->by($request->ip()));
        RateLimiter::for('mesa-join', fn (Request $request) => Limit::perMinute(15)->by($request->ip()));
    }

    /**
     * Autorización central: el administrador puede todo y cualquier
     * habilidad que coincida con un permiso del rol se concede. Las
     * policies de modelo siguen ejecutándose cuando se devuelve null.
     */
    protected function configureAuthorization(): void
    {
        Gate::before(function ($user, string $ability) {
            if ($user->isAdmin()) {
                return true;
            }

            if ($user->hasPermission($ability)) {
                return true;
            }

            return null;
        });
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
