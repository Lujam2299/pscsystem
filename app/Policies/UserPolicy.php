<?php

namespace App\Policies;

use App\Models\User;
use App\Support\Authorization\Permission;
use App\Support\Authorization\RolePermissionMap;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return RolePermissionMap::allows($user, Permission::USERS_VIEW);
    }

    public function view(User $user, User $target): bool
    {
        return (int) $user->id === (int) $target->id || $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return RolePermissionMap::allows($user, Permission::USERS_CREATE);
    }

    public function update(User $user, User $target): bool
    {
        return RolePermissionMap::allows($user, Permission::USERS_UPDATE);
    }

    public function requestVacation(User $user, User $target): bool
    {
        return (int) $user->id === (int) $target->id
            || RolePermissionMap::allows($user, Permission::VACATIONS_VIEW_ALL);
    }

    public function requestTermination(User $user, User $target): bool
    {
        return (int) $user->id === (int) $target->id
            || RolePermissionMap::allows($user, Permission::TERMINATIONS_CREATE);
    }

    public function submitComplaint(User $user, User $target): bool
    {
        return (int) $user->id === (int) $target->id;
    }
}
