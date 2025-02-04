<?php

namespace Opscale\NovaAuthorization;

use Illuminate\Http\Request;
use Laravel\Nova\Menu\MenuSection;
use Laravel\Nova\Nova;
use Laravel\Nova\Tool as NovaTool;

class Tool extends NovaTool
{
    public function boot()
    {
        Nova::script('nova-authorization', __DIR__ . '/../dist/js/tool.js');
        Nova::style('nova-authorization', __DIR__ . '/../dist/css/tool.css');
    }

    public function menu(Request $request)
    {
        return MenuSection::make('NovaAuthorization')
            ->path('nova-authorization')
            ->icon('server');
    }
}
