<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Subject;

class SubjectPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_subject');
    }

    public function view(User $user, Subject $Subject): bool
    {
        return $user->can('view_subject');
    }

    public function create(User $user): bool
    {
        return $user->can('create_subject');
    }

    public function update(User $user, Subject $Subject): bool
    {
        return $user->can('update_subject');
    }

    public function delete(User $user, Subject $Subject): bool
    {
        return $user->can('delete_subject');
    }

    public function restore(User $user, Subject $Subject): bool
    {
        return $user->can('restore_subject');
    }

    public function forceDelete(User $user, Subject $Subject): bool
    {
        return $user->can('force_delete_subject');
    }
}
