<?php

namespace Workbench\App\Providers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;

class WorkbenchServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Load workbench-specific configuration for nova-authorization
        // Using Config::set() instead of mergeConfigFrom() to ensure workbench config
        // takes priority over the package's default configuration
        $workbenchConfig = require __DIR__.'/../../config/nova-authorization.php';

        Config::set('nova-authorization', array_merge(
            Config::get('nova-authorization', []),
            $workbenchConfig
        ));
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
    }
}
