<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
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
            \App\Contracts\WhatsAppGateway::class,
            \App\Services\WhatsApp\DeepLinkWhatsApp::class,
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->configureAuthorization();
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
