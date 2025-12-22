<?php

namespace Opscale\NovaAuthorization\Contracts;

interface HasPrivileges
{
    /**
     * Determine if the user is a super admin.
     */
    public function isSuperAdmin(): bool;
}
