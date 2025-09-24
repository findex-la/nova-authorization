<?php

namespace Opscale\NovaAuthorization\Services;

use Illuminate\Support\Facades\App;
use Laravel\Nova\Actions\ActionResource;
use Laravel\Nova\Resource;
use ReflectionClass;
use Symfony\Component\Finder\Finder;

final class ResourceDiscoveryService
{
    /**
     * @return array<class-string>
     */
    final public static function getAuthorizableResources(?Finder $finder = null): array
    {
        $directory = app_path('Nova');

        if (! is_dir($directory)) {
            return [];
        }

        $namespace = App::getNamespace();
        $resources = [];

        /** @phpstan-ignore solid.dip.disallowInstantiation */
        $finder ??= new Finder;
        foreach ($finder->in($directory)->files() as $file) {
            /** @var class-string $resourceClass */
            $resourceClass = $namespace . str_replace(
                ['/', '.php'],
                ['\\', ''],
                str()->after(
                    $file->getPathname(),
                    app_path() . DIRECTORY_SEPARATOR
                )
            );

            if (! class_exists($resourceClass)) {
                continue;
            }

            $isResource = is_subclass_of($resourceClass, Resource::class);
            $isActionResource = is_subclass_of($resourceClass, ActionResource::class);
            $isAbstract = (new ReflectionClass($resourceClass))->isAbstract();

            if ($isResource && ! $isActionResource && ! $isAbstract) {
                $resources[] = $resourceClass;
            }
        }

        return $resources;
    }
}
