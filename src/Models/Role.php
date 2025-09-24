<?php

namespace Opscale\NovaAuthorization\Models;

use Enigma\ValidatorTrait;
use GeneaLabs\LaravelPivotEvents\Traits\PivotEventTrait;
use Opscale\NovaAuthorization\Services\Actions\ClearCache;
use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    use PivotEventTrait;
    use ValidatorTrait;

    /**
     * @var array<string, array<int, string>>
     */
    public array $validationRules = [
        'name' => ['required', 'string', 'max:255', 'unique:permissions'],
        'guard_name' => ['required'],
    ];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'guard_name',
    ];

    final protected static function booted(): void
    {
        static::pivotSynced(function ($model, $relationName, $changes): void {
            $userIds = $model->users->pluck('id')->toArray();
            ClearCache::dispatch($userIds);
        });
    }
}
