<?php

namespace Opscale\NovaAuthorization\Services\Actions;

use Closure;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Lorisleiva\Actions\Concerns\AsAction;
use Opscale\NovaAuthorization\Contracts\HasPrivileges;

final class CachePermission
{
    use AsAction;

    /**
     * @param  Closure(): bool  $checkCallback
     */
    final public function handle(
        HasPrivileges $hasPrivileges,
        string $action,
        string $resource,
        Closure $checkCallback
    ): bool {
        if (! $this->shouldUseCache()) {
            return $checkCallback();
        }

        $cacheKey = $this->generateCacheKey($hasPrivileges, $action, $resource);

        /** @var bool */
        return Cache::remember(
            $cacheKey,
            Carbon::now()->addHours(24),
            $checkCallback
        );
    }

    private function shouldUseCache(): bool
    {
        return Config::get('nova-authorization.cache') && Config::get('cache.default') === 'redis';
    }

    private function generateCacheKey(HasPrivileges $hasPrivileges, string $action, string $resource): string
    {
        $base = 'opscale.authorization.user.' . $hasPrivileges->getKey() . '.';
        $permission = sprintf('%s %s', $action, $resource);

        return $base . str()->slug($permission, '.');
    }
}
