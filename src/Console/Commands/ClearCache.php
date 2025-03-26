<?php

namespace Opscale\NovaAuthorization\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Opscale\NovaAuthorization\Jobs\ClearCache as ClearCacheJob;

class ClearCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'authorization:clear-cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete cached permissions for uses.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            ClearCacheJob::dispatch();
            $this->info('Authorization cache cleared successfully.');
        } catch (Exception $ex) {
            $this->error('Something went wrong, operation not completed.');
        }
    }
}
