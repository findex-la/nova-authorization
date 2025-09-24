<?php

namespace Opscale\NovaAuthorization\Console\Commands;

use Illuminate\Console\Command;
use Opscale\NovaAuthorization\Services\ResourceDiscoveryService;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class CreateRole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nova-authorization:create-role';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a role and assign permission according to CRUDX pattern.';

    /**
     * Execute the console command.
     */
    final public function handle(): int
    {
        $roleName = $this->ask('What is name of the role?');

        $role = Role::query()->firstOrCreate([
            'name' => $roleName,
            'guard_name' => 'web',
        ]);

        $resources = ResourceDiscoveryService::getAuthorizableResources();
        $permissionsMap = [
            'C' => _('Create'),
            'R' => _('Read'),
            'U' => _('Update'),
            'D' => _('Delete'),
            'X' => _('Execute'),
        ];

        foreach ($resources as $resource) {
            $resourceName = $resource::singularLabel();
            $permissions = $this->ask(sprintf('What are the permissions for %s?', $resourceName));

            if (! is_string($permissions)) {
                continue;
            }

            foreach (str_split($permissions) as $letter) {
                if (array_key_exists($letter, $permissionsMap)) {
                    $permissionName = $permissionsMap[$letter] . ' ' . $resourceName;

                    $permission = Permission::query()->firstOrCreate([
                        'name' => $permissionName,
                        'guard_name' => 'web',
                    ]);

                    $permission->assignRole($role);
                } else {
                    $this->warn(sprintf("Unknown permission code '%s' in pattern CRUDX. Skipping.", $letter));
                }
            }
        }

        $this->info('Permissions have been successfully assigned.');

        return 0;
    }
}
