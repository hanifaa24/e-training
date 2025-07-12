<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Question;

class QuestionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_question');
    }

    public function view(User $user, Question $Question): bool
    {
        return $user->can('view_question');
    }

    public function create(User $user): bool
    {
        return $user->can('create_question');
    }

    public function update(User $user, Question $Question): bool
    {
        return $user->can('update_question');
    }

    public function delete(User $user, Question $Question): bool
    {
        return $user->can('delete_question');
    }

    public function restore(User $user, Question $Question): bool
    {
        return $user->can('restore_question');
    }

    public function forceDelete(User $user, Question $Question): bool
    {
        return $user->can('force_delete_question');
    }
}
