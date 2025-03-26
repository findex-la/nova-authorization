<?php

// Optimized in-memory array
if (! function_exists('loadedAuthorizableResources')) {
    function loadedAuthorizableResources(): array
    {
        // To avoid Fully Qualified Class Names should only be used for accessing class names
        // linting error, we need to store the class names in variables.
        $novaClass = \Laravel\Nova\Nova::class;
        $appClass = \Illuminate\Support\Facades\App::class;

        $resources = collect($novaClass::$resources)
            ->filter(function ($resource) {
                $namespace = $appClass::getNamespace();

                return str_starts_with($resource, $namespace);
            });

        return $resources->toArray();
    }
}

// Copied from Laravel Nova
if (! function_exists('appAuthorizableResources')) {
    function appAuthorizableResources(): array
    {
        // To avoid Fully Qualified Class Names should only be used for accessing class names
        // linting error, we need to store the class names in variables.
        $resourceClass = \Laravel\Nova\Resource::class;
        $actionResourceClass = \Laravel\Nova\Actions\ActionResource::class;

        $directory = app_path('Nova');
        $namespace = app()->getNamespace();

        $resources = [];

        foreach ((new \Symfony\Component\Finder\Finder)
            ->in($directory)->files() as $resource) {
            $resource = $namespace . str_replace(
                ['/', '.php'],
                ['\\', ''],
                str()->after(
                    $resource->getPathname(),
                    app_path() . DIRECTORY_SEPARATOR)
            );

            $isResource = is_subclass_of(
                $resource,
                $resourceClass);
            $isActionResource = is_subclass_of(
                $resource,
                $actionResourceClass);
            $isAbstract = (new ReflectionClass($resource))->isAbstract();
            if ($isResource && ! $isActionResource && ! $isAbstract) {
                $resources[] = $resource;
            }
        }

        return $resources;
    }
}
