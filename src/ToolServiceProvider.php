<?php

namespace Opscale\NovaAuthorization;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Gate;
use Opscale\NovaAuthorization\Console\Commands\AssignRole;
use Opscale\NovaAuthorization\Console\Commands\CreatePermissions;
use Opscale\NovaAuthorization\Console\Commands\CreateRole;
use Opscale\NovaAuthorization\Console\Commands\CreateSuperAdmin;
use Opscale\NovaAuthorization\Nova\Permission;
use Opscale\NovaAuthorization\Nova\Role;
use Opscale\NovaAuthorization\Policies\Policy;
use Opscale\NovaAuthorization\Services\Actions\ClearCache;
use Opscale\NovaPackageTools\NovaPackage;
use Opscale\NovaPackageTools\NovaPackageServiceProvider;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;

class ToolServiceProvider extends NovaPackageServiceProvider
{
    /**
     * @phpstan-ignore solid.ocp.conditionalOverride
     */
    public function configurePackage(Package $package): void
    {
        /** @var NovaPackage $package */
        $package
            ->name('nova-authorization')
            ->hasConfigFile()
            ->hasCommands([
                CreatePermissions::class,
                CreateRole::class,
                AssignRole::class,
                CreateSuperAdmin::class,
                ClearCache::class,
            ])
            /** @phpstan-ignore argument.type */
            ->hasResources([Role::class, Permission::class])
            ->hasInstallCommand(function (InstallCommand $installCommand): void {
                $installCommand
                    ->publishConfigFile()
                    ->askToStarRepoOnGitHub('opscale-co/nova-authorization');
            });
    }

    /**
     * @phpstan-ignore solid.ocp.conditionalOverride
     */
    public function packageBooted(): void
    {
        parent::packageBooted();
        $this->registerPolicies();
    }

    final protected function registerPolicies(): void
    {
        /** @var array<class-string> $resources */
        $resources = Config::get('nova-authorization.resources', []);
        /** @var array<string, class-string> $predefinedPolicies */
        $predefinedPolicies = Config::get('nova-authorization.policies', []);

        foreach ($resources as $resource) {
            $resourceName = $resource::singularLabel();
            if (! is_string($resourceName)) {
                continue;
            }

            if (! isset($resource::$model)) {
                continue;
            }

            $policy = $predefinedPolicies[$resourceName] ??
                $this->generatePolicyClass($resourceName);
            Gate::policy($resource::$model, $policy);
        }
    }

    /**
     * @return class-string
     */
    final protected function generatePolicyClass(string $resource): string
    {
        $class = get_class(new class extends Policy
        {
            protected ?string $resource = null;

            public function getResource(): string
            {
                return $this->resource ?? '';
            }

            public function setResource(string $resource): void
            {
                $this->resource = $resource;
            }
        });

        $this->app->singleton($class, function ($app) use ($class, $resource): object {
            $instance = new $class;
            $instance->setResource($resource);

            return $instance;
        });

        return $class;
    }
}
