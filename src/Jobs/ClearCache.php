<?php

namespace Opscale\NovaAuthorization\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Opscale\NovaAuthorization\Services\Actions\ClearCache as ClearCacheAction;

class ClearCache implements ShouldQueue
{
    use Queueable;

    /**
     * @param  array<int>  $userIds
     */
    final public function __construct(
        public array $userIds = [],
    ) {}

    final public function handle(): void
    {
        ClearCacheAction::run($this->userIds);
    }
}
