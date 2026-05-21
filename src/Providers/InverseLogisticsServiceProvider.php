<?php

namespace Sglms\InverseLogistics\Providers;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Sglms\InverseLogistics\Services\InverseLogisticsManager;

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
        $this->loadRoutesFrom(__DIR__.'/../../routes/api.php');
        $this->loadRoutesFrom(__DIR__.'/../../routes/web.php');
        $this->loadViewsFrom(__DIR__.'/../../resources/views', 'inverse-logistics');
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
        $this->loadTranslationsFrom(__DIR__.'/../../resources/lang', 'inverse-logistics');
        $this->loadJSONTranslationsFrom(__DIR__.'/../../resources/lang');

        $this->publishes([
            __DIR__.'/../../config/inverse-logistics.php' => config_path('inverse-logistics.php'),
        ], 'inverse-logistics-config');

        $this->registerLivewireComponents();
    }

    private function registerLivewireComponents(): void
    {
        if (! class_exists('Livewire\\Livewire') || ! method_exists('Livewire\\Livewire', 'addNamespace')) {
            return;
        }

        Livewire::addNamespace(
            namespace: 'invlog',
            viewPath: __DIR__.'/../../resources/views/livewire',
            classNamespace: 'Sglms\\InverseLogistics\\Http\\Livewire',
            classPath: __DIR__.'/../../src/Http/Livewire',
            classViewPath: __DIR__.'/../../resources/views/livewire',
        );
    }
}
