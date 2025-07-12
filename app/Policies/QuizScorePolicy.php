<?php

namespace App\Policies;

use App\Models\User;
use App\Models\QuizScore;

class QuizScorePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_quiz::score');
    }

    public function view(User $user, QuizScore $QuizScore): bool
    {
        return $user->can('view_quiz::score');
    }

    public function create(User $user): bool
    {
        return $user->can('create_quiz::score');
    }

    public function update(User $user, QuizScore $QuizScore): bool
    {
        return $user->can('update_quiz::score');
    }

    public function delete(User $user, QuizScore $QuizScore): bool
    {
        return $user->can('delete_quiz::score');
    }

    public function restore(User $user, QuizScore $QuizScore): bool
    {
        return $user->can('restore_quiz::score');
    }

    public function forceDelete(User $user, QuizScore $QuizScore): bool
    {
        return $user->can('force_delete_quiz::score');
    }
}
