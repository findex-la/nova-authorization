<?php

namespace Opscale\NovaAuthorization;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Laravel\Nova\Events\ServingNova;
use Laravel\Nova\Nova;
use Opscale\NovaAuthorization\Policies\Policy;

class ToolServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->registerCommands();
        }

        Nova::serving(function (ServingNova $event) {
            $this->registerResources();
            $this->registerPolicies();
        });
    }

    public function register()
    {
        $this->registerConfigs();
    }

    protected function registerPolicies()
    {
        $resources = cache()->remember(
            'opscale.authorization.resources',
            now()->addHours(24),
            fn () => appAuthorizableResources()
        );

        $predefinedPolicies = config('nova-authorization.policies') ?? [];
        $resources = array_unique(
            array_merge($resources, array_keys($predefinedPolicies)));

        foreach ($resources as $resource) {
            $resourceName = $resource::singularLabel();
            $policy = $predefinedPolicies[$resourceName] ??
                $this->generatePolicyClass($resourceName);
            Gate::policy($resource::$model, $policy);
        }
    }

    protected function generatePolicyClass($resource)
    {
        $class = get_class(new class extends Policy
        {
            protected $resource = null;

            public function getResource()
            {
                return $this->resource;
            }

            public function setResource(string $resource)
            {
                $this->resource = $resource;
            }
        });

        $this->app->singleton($class, function ($app) use ($class, $resource) {
            $instance = new $class;
            $instance->setResource($resource);

            return $instance;
        });

        return $class;
    }

    protected function registerResources()
    {
        Nova::resources([
            \Opscale\NovaAuthorization\Nova\Role::class,
            \Opscale\NovaAuthorization\Nova\Permission::class,
        ]);
    }

    protected function registerConfigs()
    {
        $filename = 'nova-authorization.php';
        $this->mergeConfigFrom(
            __DIR__ . "/../config/{$filename}", 'nova-authorization'
        );
        $this->publishes([
            __DIR__ . "/../config/{$filename}" => config_path($filename),
        ]);
    }

    protected function registerCommands()
    {
        $this->commands([
            \Opscale\NovaAuthorization\Console\Commands\CreatePermissions::class,
            \Opscale\NovaAuthorization\Console\Commands\CreateRole::class,
            \Opscale\NovaAuthorization\Console\Commands\AssignRole::class,
            \Opscale\NovaAuthorization\Console\Commands\CreateSuperAdmin::class,
            \Opscale\NovaAuthorization\Console\Commands\ClearCache::class,
        ]);
    }
}
