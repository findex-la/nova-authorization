<?php

namespace Opscale\NovaAuthorization\Services\Actions;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Redis;
use Opscale\Actions\Action;
use Spatie\Permission\Events\RoleAttached;
use Spatie\Permission\Events\RoleDetached;

final class ClearCache extends Action
{
    final public function identifier(): string
    {
        return 'clear-cache';
    }

    final public function name(): string
    {
        return 'Clear Cache';
    }

    final public function description(): string
    {
        return 'Delete cached permissions for a user';
    }

    /**
     * @return array<int, array{name: string, description: string, type: string, rules: array<string>}>
     */
    final public function parameters(): array
    {
        return [
            [
                'name' => 'userId',
                'description' => 'User ID to clear cache for',
                'type' => 'string',
                'rules' => ['required', 'string'],
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array{success: bool, message: string}
     */
    final public function handle(array $attributes = []): array
    {
        $this->fill($attributes);
        $validated = $this->validateAttributes();

        /** @var string $userId */
        $userId = $validated['userId'];

        if (Config::get('cache.default') === 'redis') {
            $this->clearCacheByPrefix($userId);
        } else {
            Cache::flush();
        }

        return [
            'success' => true,
            'message' => 'Authorization cache cleared successfully.',
        ];
    }

    final public function asListener(RoleAttached|RoleDetached $event): void
    {
        $userId = $event->model->getKey();
        $this->handle(['userId' => (string) $userId]);
    }

    private function clearCacheByPrefix(string $userId): void
    {
        /** @var string $connectionName */
        $connectionName = Config::get('cache.stores.redis.connection', 'default');
        /** @var \Illuminate\Redis\Connections\Connection $connection */
        $connection = Redis::connection($connectionName);

        $prefix = 'opscale.authorization.user.' . $userId;

        /** @var array<string> $keys */
        $keys = $connection->keys($prefix . '.*');

        foreach ($keys as $key) {
            Cache::forget($key);
        }
    }
}
