<?php

namespace Opscale\NovaAuthorization\Services\Actions;

use Illuminate\Support\Facades\Config;
use Opscale\Actions\Action;

final class CreatePermissions extends Action
{
    final public function identifier(): string
    {
        return 'create-permissions';
    }

    final public function name(): string
    {
        return 'Create Permissions';
    }

    final public function description(): string
    {
        return 'Create permissions for all configured Nova resources';
    }

    /**
     * @return array<int, array{name: string, description: string, type: string, rules: array<string>}>
     */
    final public function parameters(): array
    {
        return [];
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array{success: bool, message: string, created?: array<string>}
     */
    final public function handle(array $attributes = []): array
    {
        /** @var array<class-string> $resources */
        $resources = Config::get('nova-authorization.resources', []);

        if (empty($resources)) {
            return [
                'success' => false,
                'message' => 'No resources configured. Please add resources to nova-authorization config.',
            ];
        }

        /** @var class-string<\Illuminate\Database\Eloquent\Model&\Spatie\Permission\Contracts\Permission> $permissionClass */
        $permissionClass = Config::get('permission.models.permission');

        $permissions = [
            __('Create'),
            __('Read'),
            __('Update'),
            __('Delete'),
            __('Execute'),
        ];

        $created = [];

        foreach ($resources as $resource) {
            $resourceName = $resource::singularLabel();
            foreach ($permissions as $permission) {
                $name = $permission.' '.$resourceName;
                $permissionClass::query()->firstOrCreate([
                    'name' => $name,
                    'guard_name' => 'web',
                ]);
            }
            $created[] = $resourceName;
        }

        return [
            'success' => true,
            'message' => 'Permissions created successfully.',
            'created' => $created,
        ];
    }
}
