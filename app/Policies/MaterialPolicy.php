<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Material;

class MaterialPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_material');
    }

    public function view(User $user, Material $Material): bool
    {
        return $user->can('view_material');
    }

    public function create(User $user): bool
    {
        return $user->can('create_material');
    }

    public function update(User $user, Material $Material): bool
    {
        return $user->can('update_material');
    }

    public function delete(User $user, Material $Material): bool
    {
        return $user->can('delete_material');
    }

    public function restore(User $user, Material $Material): bool
    {
        return $user->can('restore_material');
    }

    public function forceDelete(User $user, Material $Material): bool
    {
        return $user->can('force_delete_material');
    }
}
