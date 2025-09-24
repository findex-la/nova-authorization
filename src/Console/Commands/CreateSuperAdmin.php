<?php

namespace Opscale\NovaAuthorization\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Opscale\NovaAuthorization\Services\ResourceDiscoveryService;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class CreateSuperAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nova-authorization:super-admin';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a super admin role.';

    /**
     * Execute the console command.
     */
    final public function handle(): int
    {
        $roleName = 'Super Admin';

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
            $permissions = 'CRUDX';

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

        $assign = $this->confirm('Do you want to assign the new role to an user?');
        if ($assign) {
            /** @var class-string $userClass */
            $userClass = Config::get('auth.providers.users.model');
            $users = $userClass::all();
            $userOptions = $users->pluck('name')->toArray();
            $selectedUser = $this->choice('What user do you want to use?', $userOptions);
            $user = $users->where('name', $selectedUser)->first();
            $user->assignRole($role);
        }

        $this->info('Role has been successfully created.');

        return 0;
    }
}
