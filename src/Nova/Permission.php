<?php

namespace Opscale\NovaAuthorization\Nova;

use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Tag;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Resource;

class Permission extends Resource
{
    public static $model = \Opscale\NovaAuthorization\Models\Permission::class;

    public static $title = 'name';

    public static $displayInNavigation = false;

    public static $search = [
        'name',
    ];

    public static function label()
    {
        return _('Permissions');
    }

    public static function singularLabel()
    {
        return _('Permission');
    }

    public static function uriKey()
    {
        return _('permissions');
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

            Tag::make('Roles', 'roles', Role::class)
                ->hideFromIndex(),
        ];
    }
}
