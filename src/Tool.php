<?php

namespace Opscale\NovaAuthorization;

use Illuminate\Http\Request;
use Laravel\Nova\Menu\MenuItem;
use Laravel\Nova\Nova;
use Laravel\Nova\Tool as NovaTool;
use Opscale\NovaAuthorization\Nova\Role;

class Tool extends NovaTool
{
    public function boot()
    {
        Nova::script('nova-authorization', __DIR__ . '/../dist/js/tool.js');
        Nova::style('nova-authorization', __DIR__ . '/../dist/css/tool.css');
    }

    public function menu(Request $request)
    {
        return MenuItem::resource(Role::class);
    }
}
