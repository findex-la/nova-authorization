<?php

namespace Opscale\NovaAuthorization\Nova;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Laravel\Nova\Fields\Repeater;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Resource;
use Opscale\NovaAuthorization\Models\Role as Model;
use Opscale\NovaAuthorization\Nova\Repeatables\Permission;
use Opscale\NovaAuthorization\Nova\Repeatables\Presets\PermissionsPreset;

/**
 * @extends Resource<\Opscale\NovaAuthorization\Models\Role>
 */
class Role extends Resource
{
    /**
     * @var class-string<\Opscale\NovaAuthorization\Models\Role>
     */
    public static $model = Model::class;

    public static $title = 'name';

    public static $authorizable = true;

    public static $globallySearchable = true;

    /**
     * @var list<string>
     */
    public static $search = [
        'name',
    ];

    final public static function group(): string
    {
        return __('Authorization');
    }

    final public static function label(): string
    {
        return __('Roles');
    }

    final public static function singularLabel(): string
    {
        return __('Role');
    }

    final public static function uriKey(): string
    {
        return 'roles';
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
            Text::make(__('Name'), 'name')
                ->required()
                ->creationRules(fn (): array => $this->model()?->validationRules['name'] ?? [])
                ->sortable(),

            Select::make(__('Context'), 'guard_name')
                ->options($guards)
                ->displayUsingLabels()
                ->required()
                ->rules(fn (): array => $this->model()?->validationRules['guard_name'] ?? [])
                ->sortable()
                ->filterable(),

            Repeater::make(__('Permissions'), 'permissions')
                /** @phpstan-ignore solid.dip.disallowInstantiation */
                ->preset(new PermissionsPreset)
                ->repeatables([
                    Permission::make(),
                ])
                ->hideFromIndex(),
        ];
    }
}
