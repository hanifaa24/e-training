<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Course;

class CoursePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_course');
    }

    public function view(User $user, Course $Course): bool
    {
        return $user->can('view_course');
    }

    public function create(User $user): bool
    {
        return $user->can('create_course');
    }

    public function update(User $user, Course $Course): bool
    {
        return $user->can('update_course');
    }

    public function delete(User $user, Course $Course): bool
    {
        return $user->can('delete_course');
    }

    public function restore(User $user, Course $Course): bool
    {
        return $user->can('restore_course');
    }

    public function forceDelete(User $user, Course $Course): bool
    {
        return $user->can('force_delete_course');
    }
}
