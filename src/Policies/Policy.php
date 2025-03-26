<?php

namespace Opscale\NovaAuthorization\Policies;

use Exception;
use Illuminate\Auth\Access\HandlesAuthorization;
use Opscale\NovaAuthorization\Contracts\HasPrivileges;

abstract class Policy
{
    use HandlesAuthorization;

    abstract public function getResource();

    public function before($user, $ability)
    {
        if ($user != null && $user instanceof HasPrivileges) {
            return $user->isSuperAdmin();
        }

        return null;
    }

    public function create($user)
    {
        return $this->can($user, null, _('Create'));
    }

    public function viewAny($user)
    {
        return $this->can($user, null, _('Read'));
    }

    public function viewOwn($user, $model)
    {
        return $this->can($user, $model, _('Read'));
    }

    public function view($user, $model)
    {
        if ($this->checkUser($user, $model)) {
            return $this->viewOwn($user, $model);
        } else {
            return $this->can($user, $model, _('Read'));
        }
    }

    public function updateOwn($user, $model)
    {
        return $this->can($user, $model, _('Update'));
    }

    public function update($user, $model)
    {
        if ($this->checkUser($user, $model)) {
            return $this->updateOwn($user, $model);
        } else {
            return $this->can($user, $model, _('Update'));
        }
    }

    public function deleteOwn($user, $model)
    {
        return $this->can($user, $model, _('Delete'));
    }

    public function delete($user, $model)
    {
        if ($this->checkUser($user, $model)) {
            return $this->deleteOwn($user, $model);
        } else {
            return $this->can($user, $model, _('Delete'));
        }
    }

    public function runOwnAction($user, $model)
    {
        return $this->can($user, $model, _('Execute'));
    }

    public function runAction($user, $model)
    {
        if ($this->checkUser($user, $model)) {
            return $this->runOwnAction($user, $model);
        } else {
            return $this->can($user, $model, _('Execute'));
        }
    }

    protected function can($user, $model, $action)
    {
        if (config('nova-authorization.cache') && config('cache.default') == 'redis') {
            return $this->checkCachedPermission($user, $model, $action);
        } else {
            return $this->checkPermission($user, $model, $action);
        }
    }

    protected function checkPermission($user, $model, $action)
    {
        try {
            $resource = $this->getResource();
            $permission = "{$action} {$resource}";

            return $user->checkPermissionTo($permission);
        } catch (Exception $e) {
            return false;
        }
    }

    protected function checkCachedPermission($user, $model, $action)
    {
        try {
            $base = 'opscale.authorization.user.' . $user->id . '.';
            $resource = $this->getResource();
            $permission = "{$action} {$resource}";
            $cacheKey = $base . str()->slug($permission, '.');

            return cache()->remember(
                $cacheKey,
                now()->addHours(24),
                function () use ($user, $model, $action) {
                    return $this->checkPermission($user, $model, $action);
                });
        } catch (Exception $e) {
            return false;
        }
    }

    protected function checkUser($user, $model)
    {
        try {
            $userClass = config('auth.providers.users.model');
            $predicate = $model instanceof $userClass ? 'id' : 'user_id';

            return isset($model->$predicate) && $user->id == $model->$predicate;
        } catch (Exception $e) {
            return false;
        }
    }
}
