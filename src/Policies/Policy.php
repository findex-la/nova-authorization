<?php

namespace Opscale\NovaAuthorization\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Support\Facades\Config;
use Opscale\NovaAuthorization\Contracts\HasPrivileges;
use Opscale\NovaAuthorization\Services\Actions\CachePermission;

abstract class Policy
{
    use HandlesAuthorization;

    abstract public function getResource(): string;

    /**
     * @param  mixed  $user
     * @param  string  $ability
     */
    final public function before($user, $ability): ?bool
    {
        if ($user != null && $user instanceof HasPrivileges) {
            return $user->isSuperAdmin();
        }

        return null;
    }

    /**
     * @param  HasPrivileges  $user
     */
    final public function create($user): bool
    {
        return $this->can($user, null, _('Create'));
    }

    /**
     * @param  HasPrivileges  $user
     */
    final public function viewAny($user): bool
    {
        return $this->can($user, null, _('Read'));
    }

    /**
     * @param  HasPrivileges  $user
     * @param  mixed  $model
     */
    final public function viewOwn($user, $model): bool
    {
        return $this->can($user, $model, _('Read'));
    }

    /**
     * @param  HasPrivileges  $user
     * @param  mixed  $model
     */
    final public function view($user, $model): bool
    {
        if ($this->checkUser($user, $model)) {
            return $this->viewOwn($user, $model);
        }

        return $this->can($user, $model, _('Read'));
    }

    /**
     * @param  HasPrivileges  $user
     * @param  mixed  $model
     */
    final public function updateOwn($user, $model): bool
    {
        return $this->can($user, $model, _('Update'));
    }

    /**
     * @param  HasPrivileges  $user
     * @param  mixed  $model
     */
    final public function update($user, $model): bool
    {
        if ($this->checkUser($user, $model)) {
            return $this->updateOwn($user, $model);
        }

        return $this->can($user, $model, _('Update'));
    }

    /**
     * @param  HasPrivileges  $user
     * @param  mixed  $model
     */
    final public function deleteOwn($user, $model): bool
    {
        return $this->can($user, $model, _('Delete'));
    }

    /**
     * @param  HasPrivileges  $user
     * @param  mixed  $model
     */
    final public function delete($user, $model): bool
    {
        if ($this->checkUser($user, $model)) {
            return $this->deleteOwn($user, $model);
        }

        return $this->can($user, $model, _('Delete'));
    }

    /**
     * @param  HasPrivileges  $user
     * @param  mixed  $model
     */
    final public function runOwnAction($user, $model): bool
    {
        return $this->can($user, $model, _('Execute'));
    }

    /**
     * @param  HasPrivileges  $user
     * @param  mixed  $model
     */
    final public function runAction($user, $model): bool
    {
        if ($this->checkUser($user, $model)) {
            return $this->runOwnAction($user, $model);
        }

        return $this->can($user, $model, _('Execute'));
    }

    /**
     * @param  HasPrivileges  $user
     * @param  mixed  $model
     */
    final protected function can($user, $model, string $action): bool
    {
        $resource = $this->getResource();

        /** @var bool */
        return CachePermission::run(
            $user,
            $action,
            $resource,
            fn (): bool => $this->checkPermission($user, $model, $action)
        );
    }

    /**
     * @param  HasPrivileges  $user
     * @param  mixed  $model
     */
    final protected function checkPermission($user, $model, string $action): bool
    {
        $resource = $this->getResource();
        $permission = sprintf('%s %s', $action, $resource);

        return $user->checkPermissionTo($permission);
    }

    /**
     * @param  HasPrivileges  $user
     * @param  mixed  $model
     */
    final protected function checkUser($user, $model): bool
    {
        /** @var class-string|null $userClass */
        $userClass = Config::get('auth.providers.users.model');
        if (! $userClass || ! is_object($model)) {
            return false;
        }

        $predicate = $model instanceof $userClass ? 'id' : 'user_id';

        if (! property_exists($model, $predicate)) {
            return false;
        }

        return isset($model->$predicate) && $user->getKey() == $model->$predicate;
    }
}
