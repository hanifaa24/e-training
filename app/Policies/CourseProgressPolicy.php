<?php

namespace App\Policies;

use App\Models\User;
use App\Models\CourseProgress;

class CourseProgressPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_course::progress');
    }

    public function view(User $user, CourseProgress $CourseProgress): bool
    {
        return $user->can('view_course::progress');
    }

    public function create(User $user): bool
    {
        return $user->can('create_course::progress');
    }

    public function update(User $user, CourseProgress $CourseProgress): bool
    {
        return $user->can('update_course::progress');
    }

    public function delete(User $user, CourseProgress $CourseProgress): bool
    {
        return $user->can('delete_course::progress');
    }

    public function restore(User $user, CourseProgress $CourseProgress): bool
    {
        return $user->can('restore_course::progress');
    }

    public function forceDelete(User $user, CourseProgress $CourseProgress): bool
    {
        return $user->can('force_delete_course::progress');
    }
}
