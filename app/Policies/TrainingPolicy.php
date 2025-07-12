<?php

namespace App\Policies;

use App\Models\Training;
use App\Models\User;

class TrainingPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_training');
    }

    public function view(User $user, Training $training): bool
    {
        return $user->can('view_training');
    }

    public function create(User $user): bool
    {
        return $user->can('create_training');
    }

    public function update(User $user, Training $training): bool
    {
        return $user->can('update_training');
    }

    public function delete(User $user, Training $training): bool
    {
        return $user->can('delete_training');
    }

    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_training');
    }

    public function restore(User $user, Training $training): bool
    {
        return $user->can('restore_training');
    }

    public function forceDelete(User $user, Training $training): bool
    {
        return $user->can('force_delete_training');
    }

    public function replicate(User $user): bool
    {
        return $user->can('replicate_training');
    }

    public function reorder(User $user): bool
    {
        return $user->can('reorder_training');
    }
}
