<?php

namespace Opscale\NovaAuthorization\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class CreateRole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'authorization:create-role';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a role and assign permission according to CRUDX pattern.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            $roleName = $this->ask('What is name of the role?');

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
                $permissions = $this->ask("What are the permissions for {$resourceName}?");

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

            $this->info('Permissions have been successfully assigned.');
        } catch (Exception $ex) {
            $this->error('Something went wrong, operation not completed.');
        }
    }
}
