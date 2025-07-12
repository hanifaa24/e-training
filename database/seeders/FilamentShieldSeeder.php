<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class FilamentShieldSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'view_user', 'view_any_user', 'create_user', 'update_user', 'delete_user',
            'force_delete_user', 'restore_user', 'replicate_user', 'reorder_user',
            'view_role', 'view_any_role', 'create_role', 'update_role', 'delete_role',
            'force_delete_role', 'restore_role', 'replicate_role', 'reorder_role',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->syncPermissions($permissions);
    }
}
