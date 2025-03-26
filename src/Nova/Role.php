<?php

namespace Opscale\NovaAuthorization\Nova;

use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Tag;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Resource;

class Role extends Resource
{
    public static $model = \Opscale\NovaAuthorization\Models\Role::class;

    public static $title = 'name';

    public static $group = 'Authorization';

    public static $search = [
        'name',
    ];

    public static function label()
    {
        return __('Roles');
    }

    public static function singularLabel()
    {
        return __('Role');
    }

    public static function uriKey()
    {
        return _('roles');
    }

    public function fields(NovaRequest $request)
    {
        $guards = collect(config('auth.guards'))
            ->mapWithKeys(function ($value, $key) {
                return [$key => $key];
            });

        return [
            Text::make(_('Name'), 'name')
                ->required()
                ->creationRules(static::$model::rules('name'))
                ->sortable(),

            Select::make(_('Context'), 'guard_name')
                ->options($guards)
                ->displayUsingLabels()
                ->required()
                ->rules(static::$model::rules('guard_name'))
                ->sortable()
                ->filterable(),

            Tag::make(_('Permissions'), 'permissions', Permission::class)
                ->hideFromIndex(),
        ];
    }
}
