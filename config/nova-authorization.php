<?php

return [
    /*
     * Determines if the package should use the cache to resolve the permissions.
     */
    'cache' => true,

    /*
     * Predefined policies to load if you want custom logic for permissions.
     * The policy classes should extend from Opscale\NovaAuthorization\Policies\Policy.
     * The key should be the resource name and the value should be the policy class.
     */
    'policies' => [],
];
