<?php

namespace Opscale\NovaAuthorization\Nova\Repeatables;

use Illuminate\Support\Collection;
use Laravel\Nova\Fields\MultiSelect;
use Laravel\Nova\Fields\Repeater\Repeatable;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Http\Requests\NovaRequest;

class Permission extends Repeatable
{
    /**
     * @return array<int, \Laravel\Nova\Fields\Field>
     */
    final public function fields(NovaRequest $request): array
    {
        /** @var array<string, string> $resources */
        $resources = (new Collection(config('nova-authorization.resources', [])))
            ->mapWithKeys(function (string $resource): array {
                /** @var class-string<\Laravel\Nova\Resource<\Illuminate\Database\Eloquent\Model>> $resource */
                return [$resource::singularLabel() => $resource::singularLabel()];
            })->toArray();

        return [
            Select::make(__('Resource'), 'resource')
                ->options($resources)
                ->displayUsingLabels()
                ->rules('required'),

            MultiSelect::make(__('Actions'), 'actions')
                ->options([
                    __('Create') => __('Create'),
                    __('Read') => __('Read'),
                    __('Update') => __('Update'),
                    __('Delete') => __('Delete'),
                    __('Execute') => __('Execute'),
                ])
                ->displayUsingLabels()
                ->rules('required'),
        ];
    }
}
