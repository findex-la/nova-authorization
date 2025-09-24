<?php

namespace Opscale\NovaAuthorization\Services\Actions;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Redis;
use Lorisleiva\Actions\Concerns\AsAction;
use Lorisleiva\Actions\Concerns\AsCommand;
use Lorisleiva\Actions\Concerns\AsJob;

final class ClearCache
{
    use AsAction;
    use AsCommand;
    use AsJob;

    public string $commandSignature = 'nova-authorization:clear-cache';

    public string $commandDescription = 'Delete cached permissions for users.';

    /**
     * @param  array<int>  $userIds
     */
    final public function handle(array $userIds = []): void
    {
        if (Config::get('cache.default') !== 'redis') {
            return;
        }

        $prefixes = $this->generatePrefixes($userIds);
        $this->clearCacheByPrefixes($prefixes);
    }

    final public function asCommand(): void
    {
        $this->handle([]);
        /** @phpstan-ignore method.notFound */
        $this->info('Authorization cache cleared successfully.');
    }

    /**
     * @param  array<int>  $userIds
     * @return array<string>
     */
    private function generatePrefixes(array $userIds): array
    {
        $prefixes = [];

        if ($userIds !== []) {
            foreach ($userIds as $userId) {
                $prefixes[] = 'opscale.authorization.user.' . $userId;
            }
        } else {
            $prefixes[] = 'opscale.authorization.user';
        }

        return $prefixes;
    }

    /**
     * @param  array<string>  $prefixes
     */
    private function clearCacheByPrefixes(array $prefixes): void
    {
        /** @var string $connectionName */
        $connectionName = Config::get('cache.stores.redis.connection', 'default');
        /** @var \Illuminate\Redis\Connections\Connection $connection */
        $connection = Redis::connection($connectionName);

        foreach ($prefixes as $prefix) {
            /** @var array<string> $keys */
            $keys = $connection->keys($prefix . '.*');
            foreach ($keys as $key) {
                Cache::forget($key);
            }
        }
    }
}
