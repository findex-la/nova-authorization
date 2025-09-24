<?php

namespace Opscale\NovaAuthorization\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Spatie\Permission\Models\Permission;

class CreatePermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nova-authorization:create-permissions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create all permissions for your Nova app.';

    /**
     * Execute the console command.
     */
    final public function handle(): int
    {
        /** @var array<class-string> $resources */
        $resources = Config::get('nova-authorization.resources', []);
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
                Permission::query()->firstOrCreate([
                    'name' => $name,
                    'guard_name' => 'web',
                ]);
            }
        }

        $this->info('Permissions have been successfully created.');

        return 0;
    }
}
