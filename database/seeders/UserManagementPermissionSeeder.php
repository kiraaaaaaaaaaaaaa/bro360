<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Database\Seeder;

class UserManagementPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            [
                'name' => 'user-management.view',
                'module' => 'User Management',
                'description' => 'View user accounts.',
            ],
            [
                'name' => 'user-management.create',
                'module' => 'User Management',
                'description' => 'Create user accounts.',
            ],
            [
                'name' => 'user-management.update',
                'module' => 'User Management',
                'description' => 'Edit user accounts.',
            ],
            [
                'name' => 'user-management.status',
                'module' => 'User Management',
                'description' => 'Activate or deactivate user accounts.',
            ],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                ['name' => $permission['name']],
                $permission
            );
        }

        $superAdmin = Role::where('name', 'Super Administrator')->first();

        if ($superAdmin) {
            $superAdmin->permissions()->syncWithoutDetaching(
                Permission::whereIn(
                    'name',
                    array_column($permissions, 'name')
                )->pluck('id')
            );
        }
    }
}