<?php

namespace Opscale\NovaAuthorization\Models;

use Spatie\Permission\Models\Permission as SpatiePermission;

class Permission extends SpatiePermission
{
    protected $fillable = [
        'name',
        'guard_name',
    ];

    protected static function rules(string $property)
    {
        return [
            'name' => ['required', 'string', 'max:255', 'unique:permissions'],
            'guard_name' => ['required'],
        ];

        return isset($rules[$property]) ? $rules[$property] : null;
    }
}
