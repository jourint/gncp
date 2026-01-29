<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;
use Illuminate\Database\Eloquent\Relations\Relation;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        //    $this->configureDebugbar();

        // Observers
        \App\Models\MaterialMovement::observe(\App\Observers\MaterialMovementObserver::class);
        \App\Models\ShoeModel::observe(\App\Observers\ShoeModelObserver::class);

        // Relations
        Relation::enforceMorphMap([
            'material'       => \App\Models\Material::class,
            'sole'           => \App\Models\ShoeSoleItem::class,
            'order_employee' => \App\Models\OrderEmployee::class,
        ]);
    }

    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(
            fn(): ?Password => app()->isProduction()
                ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
                : null
        );
    }

    protected function configureDebugbar(): void
    {
        // Выключаем по умолчанию для всех
        // \Illuminate\Support\Facades\Config::set('debugbar.enabled', false);

    }
}
