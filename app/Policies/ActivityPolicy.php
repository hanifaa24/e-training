<?php

namespace App\Policies;

use App\Models\Spatie\Activitylog\Models\Activity;
use App\Models\activitylog;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ActivityPolicy
{
    /**
     * Determine whether the activitylog can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_activitylog');
    }

    /**
     * Determine whether the activitylog can view the model.
     */
     public function view(User $user): bool
    {
        return $user->can('view_activitylog');
    }

    public function create(User $user): bool
    {
        return $user->can('create_activitylog');
    }

    /**
     * Determine whether the activitylog can update the model.
     *
     * @param  \App\Models\activitylog  $user
     * @return bool
     */
    public function update(User $user): bool
    {
        return $user->can('update_activitylog');
    }

    /**
     * Determine whether the activitylog can delete the model.
     *
     * @param  \App\Models\activitylog  $user
     * @return bool
     */
    public function delete(User $user): bool
    {
        return $user->can('delete_activitylog');
    }

    /**
     * Determine whether the activitylog can bulk delete.
     *
     * @param  \App\Models\activitylog  $user
     * @return bool
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_activitylog');
    }

    /**
     * Determine whether the activitylog can permanently delete.
     *
     * @param  \App\Models\activitylog  $user
     * @return bool
     */
    public function forceDelete(User $user): bool
    {
        return $user->can('force_delete_activitylog');
    }

    /**
     * Determine whether the activitylog can permanently bulk delete.
     *
     * @param  \App\Models\activitylog  $user
     * @return bool
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_activitylog');
    }

    /**
     * Determine whether the activitylog can restore.
     *
     * @param  \App\Models\activitylog  $user
     * @return bool
     */
    public function restore(User $user): bool
    {
        return $user->can('restore_activitylog');
    }

    /**
     * Determine whether the activitylog can bulk restore.
     *
     * @param  \App\Models\activitylog  $user
     * @return bool
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_activitylog');
    }

    /**
     * Determine whether the activitylog can bulk restore.
     *
     * @param  \App\Models\activitylog  $user
     * @return bool
     */
    public function replicate(User $user): bool
    {
        return $user->can('replicate_activitylog');
    }

    /**
     * Determine whether the activitylog can reorder.
     *
     * @param  \App\Models\activitylog  $user
     * @return bool
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_activitylog');
    }
}
