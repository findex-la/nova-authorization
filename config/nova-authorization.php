<?php

return [
    /*
     * Determines if the package should use the cache to resolve the permissions.
     */
    'cache' => true,

    /*
    |--------------------------------------------------------------------------
    | Nova Authorization Resources
    |--------------------------------------------------------------------------
    |
    | Here you may specify which Nova resources should be exposed secured
    | by authorization policies. List the fully qualified class names of the resources
    | that you want to restrict.
    |
    */

    'resources' => [
        // Example:
        // \App\Nova\User::class,
        // \App\Nova\Post::class,
    ],

    /*
     * Predefined policies to load if you want custom logic for permissions.
     * The policy classes should extend from Opscale\NovaAuthorization\Policies\Policy.
     */
    'policies' => [
        // Example:
        // \App\Nova\User::class => \App\Policies\UserPolicy::class,
        // \App\Nova\Post::class => \App\Policies\PostPolicy::class,
    ],
];
