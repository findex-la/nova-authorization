<?php

namespace Opscale\NovaAuthorization\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Laravel\Nova\Nova;
use Opscale\NovaAuthorization\Tool;

class Authorize
{
    /**
     * @param  Closure(Request): mixed  $next
     */
    final public function handle(Request $request, Closure $next): mixed
    {
        $tools = Collection::make(Nova::registeredTools());
        $tool = $tools->first([$this, 'matchesTool']);

        if ($tool && $tool->authorize($request)) {
            return $next($request);
        }

        return abort(403);
    }

    final public function matchesTool(mixed $tool): bool
    {
        return $tool instanceof Tool;
    }
}
