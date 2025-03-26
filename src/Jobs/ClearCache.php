<?php

namespace Opscale\NovaAuthorization\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;

class ClearCache implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public array $userIds = [],
    ) {}

    public function handle(): void
    {
        if (config('cache.default') !== 'redis') {
            return;
        }

        $prefixes = [];

        if (count($this->userIds) > 0) {
            foreach ($userIds as $userId) {
                $prefixes[] = 'opscale.authorization.user.' . $userId;
            }
        } else {
            $prefixes[] = 'opscale.authorization.user';
        }

        $redis = Redis::connection(config('cache.stores.redis.connection', 'default'));
        foreach ($prefixes as $prefix) {
            $keys = $redis->keys($prefix . '.*');
            foreach ($keys as $key) {
                Cache::forget($key);
            }
        }
    }
}
