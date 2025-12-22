<?php

namespace Opscale\NovaAuthorization\Services\Actions;

use Illuminate\Support\Facades\File;
use Opscale\Actions\Action;

final class ConfigurePermissions extends Action
{
    final public function identifier(): string
    {
        return 'configure-permissions';
    }

    final public function name(): string
    {
        return 'Configure Permissions';
    }

    final public function description(): string
    {
        return 'Configure Spatie Permission models to use Nova Authorization models';
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
     * @return array{success: bool, message: string}
     */
    final public function handle(array $attributes = []): array
    {
        $configPath = config_path('permission.php');

        if (! File::exists($configPath)) {
            return [
                'success' => false,
                'message' => 'Permission config file not found. Please publish it first.',
            ];
        }

        $config = File::get($configPath);

        $replacements = [
            'Spatie\Permission\Models\Permission::class' => 'Opscale\NovaAuthorization\Models\Permission::class',
            'Spatie\Permission\Models\Role::class' => 'Opscale\NovaAuthorization\Models\Role::class',
            "'register_permission_check_method' => true," => "'register_permission_check_method' => false,",
            "'events_enabled' => false," => "'events_enabled' => true,",
        ];

        $modified = str_replace(array_keys($replacements), array_values($replacements), $config);

        if ($config !== $modified) {
            File::put($configPath, $modified);

            return [
                'success' => true,
                'message' => 'Permission configuration updated successfully.',
            ];
        }

        return [
            'success' => true,
            'message' => 'Permission configuration is already up to date.',
        ];
    }
}
