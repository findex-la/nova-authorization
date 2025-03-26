<?php

namespace Opscale\NovaAuthorization\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class CreateSuperAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'authorization:super-admin';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a super admin role.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            $roleName = 'Super Admin';

            $role = Role::firstOrCreate(
                [
                    'name' => $roleName,
                    'guard_name' => 'web',
                ]);

            $resources = appAuthorizableResources();
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

                        $permission = Permission::firstOrCreate(
                            [
                                'name' => $permissionName,
                                'guard_name' => 'web',
                            ]);

                        $permission->assignRole($role);
                    } else {
                        $this->warn("Unknown permission code '{$letter}' in pattern CRUDX. Skipping.");
                    }
                }
            }

            $assign = $this->confirm('Do you want to assign the new role to an user?');
            if ($assign) {
                $userClass = config('auth.providers.users.model');
                $users = $userClass::all();
                $userOptions = $users->pluck('name')->toArray();
                $selectedUser = $this->choice('What user do you want to use?', $userOptions);
                $user = $users->where('name', $selectedUser)->first();
                $user->assignRole($role);
            }

            $this->info('Role has been successfully created.');
        } catch (Exception $ex) {
            $this->error('Something went wrong, operation not completed.');
        }
    }
}
