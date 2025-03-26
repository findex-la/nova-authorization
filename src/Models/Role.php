<?php

namespace Opscale\NovaAuthorization\Models;

use GeneaLabs\LaravelPivotEvents\Traits\PivotEventTrait;
use Opscale\NovaAuthorization\Jobs\ClearCache;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    use PivotEventTrait;

    protected $fillable = [
        'name',
        'guard_name',
    ];

    protected static function booted()
    {
        static::pivotSynced(function ($model, $relationName, $changes) {
            $userIds = $model->users->pluck('id')->toArray();
            ClearCache::dispatchSync($userIds);
        });
    }

    protected static function rules(string $property)
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:roles'],
            'guard_name' => ['required'],
        ];

        return isset($rules[$property]) ? $rules[$property] : null;
    }
}
