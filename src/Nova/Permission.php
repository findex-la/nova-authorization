<?php

namespace Opscale\NovaAuthorization\Nova;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Resource;
use Opscale\NovaAuthorization\Models\Permission as Model;
use Opscale\NovaAuthorization\Nova\Fields\RoleTag;

/**
 * @extends Resource<\Opscale\NovaAuthorization\Models\Permission>
 */
class Permission extends Resource
{
    /**
     * @var class-string<\Opscale\NovaAuthorization\Models\Permission>
     */
    public static $model = Model::class;

    public static $title = 'name';

    public static $displayInNavigation = false;

    public static $authorizable = true;

    /**
     * @var list<string>
     */
    public static $search = [
        'name',
    ];

    final public static function label(): string
    {
        return __('Permissions');
    }

    final public static function singularLabel(): string
    {
        return __('Permission');
    }

    final public static function uriKey(): string
    {
        return 'permissions';
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

            RoleTag::make(__('Roles'))
                ->hideFromIndex(),
        ];
    }
}
