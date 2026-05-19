<?php

namespace SGLMS\InverseLogistics\Providers;

use Illuminate\Support\ServiceProvider;
use SGLMS\InverseLogistics\Services\InverseLogisticsManager;

class InverseLogisticsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../../config/inverse-logistics.php',
            'inverse-logistics'
        );

        $this->app->singleton(
            'inverse-logistics',
            fn () => new InverseLogisticsManager(
                config('inverse-logistics')
            )
        );
    }

    public function boot(): void
    {
        $this->registerLivewireComponents();
        $this->publishes([
            __DIR__.'/../../config/inverse-logistics.php' => config_path('inverse-logistics.php'),
        ], 'inverse-logistics-config');

        $this->loadRoutesFrom(__DIR__.'/../../routes/api.php');

        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');

        $this->loadViewsFrom(
            __DIR__.'/../../resources/views',
            'inverse-logistics'
        );

        $this->loadTranslationsFrom(
            __DIR__.'/../../resources/lang',
            'inverse-logistics'
        );
        Livewire::addNamespace(
            namespace: 'invlog',
            viewPath: __DIR__.'/../../resources/views/livewire',
            classNamespace: 'Sglms\\InverseLogistics\\Http\\Livewire',
            classPath: __DIR__.'/../../src/Http/Livewire',
            classViewPath: __DIR__.'/../../resources/views/livewire',
        );

    }
}
