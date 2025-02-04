<?php

namespace Opscale\NovaAuthorization;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Laravel\Nova\Events\ServingNova;
use Laravel\Nova\Nova;
use Opscale\NovaAuthorization\Http\Middleware\Authorize;

class ToolServiceProvider extends ServiceProvider
{
    public function boot()
    {
        /*$this->loadConfigs();
        $this->loadCommands();
        $this->loadMigrations();

        Nova::serving(function (ServingNova $event) {
            $this->loadResources();
            $this->loadRoutes();
        });*/
    }

    public function register()
    {
        //
    }

    /*protected function loadConfigs()
    {
        $filename = '';
        $this->publishes([
            __DIR__."/../config/$filename" => config_path($filename),
        ]);
    }

    protected function loadCommands()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([]);
        }
    }

    protected function loadMigrations()
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->publishesMigrations([
            __DIR__.'/../database/migrations' => database_path('migrations'),
        ]);
    }

    protected function loadResources()
    {
        Nova::resources([]);
    }

    protected function loadRoutes()
    {
        if ($this->app->routesAreCached()) {
            return;
        }

        Route::middleware(['nova', Authorize::class])
                ->prefix('nova-vendor/opscale-co/nova-authorization')
                ->group(__DIR__.'/../routes/api.php');
    }*/
}
