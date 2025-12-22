<?php

namespace Opscale\NovaAuthorization\Nova\Repeatables\Presets;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Laravel\Nova\Fields\Repeater\Presets\Preset;
use Laravel\Nova\Fields\Repeater\RepeatableCollection;
use Laravel\Nova\Http\Requests\NovaRequest;
use Opscale\NovaAuthorization\Models\Permission;
use Opscale\NovaAuthorization\Models\Role;

class PermissionsPreset implements Preset
{
    /**
     * The permission name format
     */
    protected string $format;

    /**
     * Create a new preset instance
     *
     * @param  string  $format  The permission name format
     */
    final public function __construct(string $format = '{action} {resource}')
    {
        $this->format = $format;
    }

    /**
     * Save the field value to permanent storage.
     *
     * @param  \Illuminate\Database\Eloquent\Model|\Laravel\Nova\Support\Fluent  $model
     */
    final public function set(
        NovaRequest $request,
        string $requestAttribute,
        $model,
        string $attribute,
        RepeatableCollection $repeatables,
        string|int|null $uniqueField
    ): callable {
        return function () use ($request, $requestAttribute, $model, $uniqueField): void {
            // Only process if model is a Role
            if (! $model instanceof Role) {
                return;
            }

            /** @var array<int, array<string, mixed>> $inputData */
            $inputData = $request->input($requestAttribute, []);
            /** @phpstan-ignore smells.helpersRestriction.helper */
            $repeaterItemsInput = collect($inputData);
            /** @phpstan-ignore smells.helpersRestriction.helper */
            $existingPermissions = collect();

            // If using unique field, handle updates properly
            if ($uniqueField) {
                $existingPermissions = $this->getExistingPermissions($model);
            }

            $permissionNames = [];

            // Process each repeater item
            $repeaterItemsInput->each(function (array $item) use ($model, &$permissionNames, $uniqueField): void {
                // Skip if not the correct type
                if (($item['type'] ?? '') !== 'permission') {
                    return;
                }

                /** @var array<string, mixed> $fields */
                $fields = $item['fields'] ?? [];
                /** @var string $resource */
                $resource = $fields['resource'] ?? '';
                /** @var string $actionsJson */
                $actionsJson = $fields['actions'] ?? '';
                /** @var array<string> $actions */
                $actions = json_decode($actionsJson, true) ?? [];

                // Handle unique field if specified
                if ($uniqueField && empty($fields[$uniqueField])) {
                    $fields[$uniqueField] = Str::uuid();
                }

                // Generate permission names for each action
                foreach ($actions as $action) {
                    $permissionName = $this->buildPermissionName($action, $resource);

                    // Create permission if it doesn't exist
                    Permission::query()->firstOrCreate([
                        'name' => $permissionName,
                        'guard_name' => $model->guard_name ?? 'web',
                    ]);

                    $permissionNames[] = $permissionName;
                }
            });

            // Sync all permissions at once
            $model->syncPermissions($permissionNames);
        };
    }

    /**
     * Retrieve the value from storage and hydrate the field's value.
     *
     * @param  \Illuminate\Database\Eloquent\Model|\Laravel\Nova\Support\Fluent  $model
     * @return Collection<int, \Laravel\Nova\Fields\Repeater\Repeatable>
     */
    final public function get(NovaRequest $request, $model, string $attribute, RepeatableCollection $repeatables): Collection
    {
        // Return empty collection if not a Role
        if (! $model instanceof Role) {
            /** @phpstan-ignore smells.helpersRestriction.helper */
            return collect();
        }

        // Ensure permissions are loaded
        $model->loadMissing('permissions');

        // Parse and group permissions
        /** @var \Illuminate\Database\Eloquent\Collection<int, Permission> $permissions */
        $permissions = $model->permissions;
        $groupedPermissions = $this->parseAndGroupPermissions($permissions);

        // Build the collection using RepeatableCollection's methods
        $items = $groupedPermissions->map(function (Collection $actions, string $resource): array {
            return [
                'type' => 'permission',
                'fields' => [
                    'resource' => ucfirst($resource),
                    'actions' => $actions->map(fn (string $action): string => ucfirst($action))->values()->toArray(),
                ],
            ];
        })->values();

        return RepeatableCollection::make($items)
            ->map(static fn (array $block) => $repeatables->newRepeatableByKey($block['type'], $block['fields']));
    }

    /**
     * Build permission name using the configured format
     */
    final protected function buildPermissionName(string $action, string $resource): string
    {
        return str_replace(
            ['{action}', '{resource}'],
            [$action, $resource],
            $this->format
        );
    }

    /**
     * Parse and group permissions by resource
     *
     * @param  \Illuminate\Database\Eloquent\Collection<int, Permission>  $permissions
     * @return Collection<string, Collection<int, string>>
     */
    final protected function parseAndGroupPermissions($permissions): Collection
    {
        return $permissions->groupBy(function (Permission $permission): string {
            // Parse permission name based on format
            $pattern = str_replace(
                ['{action}', '{resource}'],
                ['(.+?)', '(.+)'],
                $this->format
            );

            preg_match('/^' . $pattern . '$/i', $permission->name, $matches);

            if (count($matches) >= 3) {
                return $matches[2]; // resource
            }

            // Fallback for permissions that don't match the pattern
            $parts = explode(' ', $permission->name);

            return count($parts) > 1 ? $parts[1] : 'other';
        })->map(function ($permissions) {
            return $permissions->map(function (Permission $permission): string {
                // Extract action from permission name
                $pattern = str_replace(
                    ['{action}', '{resource}'],
                    ['(.+?)', '(.+)'],
                    $this->format
                );

                preg_match('/^' . $pattern . '$/i', $permission->name, $matches);

                if (count($matches) >= 2) {
                    return $matches[1]; // action
                }

                // Fallback
                $parts = explode(' ', $permission->name);

                return $parts[0];
            });
        });
    }

    /**
     * Get existing permissions from the model
     *
     * @return Collection<int, array{name: string, guard_name: string|null}>
     */
    final protected function getExistingPermissions(Role $role): Collection
    {
        /** @var \Illuminate\Database\Eloquent\Collection<int, Permission> $permissions */
        $permissions = $role->permissions;

        return $permissions->map(function (Permission $permission): array {
            return [
                'name' => $permission->name,
                'guard_name' => $permission->guard_name,
            ];
        });
    }
}
