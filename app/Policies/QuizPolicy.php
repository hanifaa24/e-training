<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Quiz;

class QuizPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_quiz');
    }

    public function view(User $user, Quiz $Quiz): bool
    {
        return $user->can('view_quiz');
    }

    public function create(User $user): bool
    {
        return $user->can('create_quiz');
    }

    public function update(User $user, Quiz $Quiz): bool
    {
        return $user->can('update_quiz');
    }

    public function delete(User $user, Quiz $Quiz): bool
    {
        return $user->can('delete_quiz');
    }

    public function restore(User $user, Quiz $Quiz): bool
    {
        return $user->can('restore_quiz');
    }

    public function forceDelete(User $user, Quiz $Quiz): bool
    {
        return $user->can('force_delete_quiz');
    }
}
