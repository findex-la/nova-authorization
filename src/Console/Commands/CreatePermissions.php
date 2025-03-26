<?php

namespace Opscale\NovaAuthorization\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;

class CreatePermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'authorization:create-permissions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create all permissions for your Nova app.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            $resources = appAuthorizableResources();
            $permissions = [
                _('Create'),
                _('Read'),
                _('Update'),
                _('Delete'),
                _('Execute'),
            ];

            foreach ($resources as $resource) {
                foreach ($permissions as $permission) {
                    $name = $permission . ' ' . $resource::singularLabel();
                    Permission::firstOrCreate(
                        [
                            'name' => $name,
                            'guard_name' => 'web',
                        ]);
                }
            }

            $this->info('Permissions have been successfully created.');
        } catch (Exception $ex) {
            $this->error('Something went wrong, operation not completed.');
        }
    }
}
