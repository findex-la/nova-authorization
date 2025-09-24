<?php

namespace Opscale\NovaAuthorization;

use Illuminate\Http\Request;
use Laravel\Nova\Menu\MenuItem;
use Laravel\Nova\Nova;
use Laravel\Nova\Tool as NovaTool;
use Opscale\NovaAuthorization\Nova\Role;
use Override;

class Tool extends NovaTool
{
    #[Override]
    final public function boot(): void
    {
        parent::boot();
        Nova::script('nova-authorization', __DIR__ . '/../dist/js/tool.js');
        Nova::style('nova-authorization', __DIR__ . '/../dist/css/tool.css');
    }

    final public function menu(Request $request): MenuItem
    {
        return MenuItem::resource(Role::class);
    }
}
