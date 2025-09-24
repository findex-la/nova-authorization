<?php

namespace Opscale\NovaAuthorization\Nova;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Tag;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Resource;

/**
 * @extends Resource<\Opscale\NovaAuthorization\Models\Permission>
 */
class Permission extends Resource
{
    /**
     * @var class-string<\Opscale\NovaAuthorization\Models\Permission>
     */
    public static $model = \Opscale\NovaAuthorization\Models\Permission::class;

    public static $title = 'name';

    public static $displayInNavigation = false;

    /**
     * @var list<string>
     */
    public static $search = [
        'name',
    ];

    final public static function label(): string
    {
        return _('Permissions');
    }

    final public static function singularLabel(): string
    {
        return _('Permission');
    }

    final public static function uriKey(): string
    {
        return _('permissions');
    }

    /**
     * @return array<\Laravel\Nova\Fields\Field>
     */
    final public function fields(NovaRequest $request): array
    {
        /** @var array<string, mixed> $authGuards */
        $authGuards = Config::get('auth.guards', []);
        $guards = Collection::make($authGuards)
            ->mapWithKeys(function ($value, $key): array {
                return [$key => $key];
            });

        return [
            Text::make(_('Name'), 'name')
                ->required()
                ->creationRules(['required', 'string', 'max:255', 'unique:permissions'])
                ->sortable(),

            Select::make(_('Context'), 'guard_name')
                ->options($guards)
                ->displayUsingLabels()
                ->required()
                ->rules(['required'])
                ->sortable()
                ->filterable(),

            Tag::make('Roles', 'roles', Role::class)
                ->hideFromIndex(),
        ];
    }
}
