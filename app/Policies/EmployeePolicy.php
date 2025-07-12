<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Employee;

class EmployeePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_employee');
    }

    public function view(User $user, Employee $employee): bool
    {
        return $user->can('view_employee');
    }

    public function create(User $user): bool
    {
        return $user->can('create_employee');
    }

    public function update(User $user, Employee $employee): bool
    {
        return $user->can('update_employee');
    }

    public function delete(User $user, Employee $employee): bool
    {
        return $user->can('delete_employee');
    }

    public function restore(User $user, Employee $employee): bool
    {
        return $user->can('restore_employee');
    }

    public function forceDelete(User $user, Employee $employee): bool
    {
        return $user->can('force_delete_employee');
    }
}
