<?php

namespace Opscale\NovaAuthorization\Nova\Fields;

use Closure;
use Laravel\Nova\Fields\Tag;
use Laravel\Nova\Http\Requests\NovaRequest;
use Opscale\NovaAuthorization\Nova\Role;
use Override;
use Stringable;

class RoleTag extends Tag
{
    /**
     * Create a new field.
     *
     * @param  Stringable|string  $name
     */
    public function __construct($name, ?string $attribute = null, ?string $resource = null)
    {
        parent::__construct($name, $attribute ?? 'roles', $resource ?? Role::class);
    }

    /**
     * Hydrate the given attribute on the model based on the incoming request.
     *
     * Uses assignRole/removeRole instead of sync() to ensure
     * RoleAttached and RoleDetached events are fired.
     *
     * @param  \Illuminate\Database\Eloquent\Model|\Laravel\Nova\Support\Fluent  $model
     */
    #[Override]
    protected function fillAttributeFromRequest(NovaRequest $request, string $requestAttribute, object $model, string $attribute): Closure
    {
        return function () use ($model, $attribute, $request, $requestAttribute): void {
            $newRoleIds = collect($this->prepareRelations($request, $requestAttribute))
                ->map(fn ($id) => (int) $id)
                ->all();

            $currentRoleIds = $model->{$attribute}()->pluck('id')->all();

            $toAttach = array_diff($newRoleIds, $currentRoleIds);
            $toDetach = array_diff($currentRoleIds, $newRoleIds);

            if (! empty($toAttach)) {
                $model->assignRole($toAttach);
            }

            foreach ($toDetach as $roleId) {
                $model->removeRole($roleId);
            }
        };
    }
}
