<?php

namespace Opscale\NovaAuthorization\Contracts;

interface HasPrivileges
{
    /**
     * Get the user's ID.
     *
     * @return int|string
     */
    public function getKey();

    /**
     * Determine if the user is a super admin.
     */
    public function isSuperAdmin(): bool;

    /**
     * Check if the user has a specific permission.
     */
    public function checkPermissionTo(string $permission): bool;
}
