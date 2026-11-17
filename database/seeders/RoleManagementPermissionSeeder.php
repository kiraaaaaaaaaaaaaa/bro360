<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Permission;

class RoleManagementPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            [
                'name' => 'role-management.view',
                'module' => 'Role Management',
                'description' => 'View roles and their permissions.',
            ],
            [
                'name' => 'role-management.create',
                'module' => 'Role Management',
                'description' => 'Create new roles.',
            ],
            [
                'name' => 'role-management.update',
                'module' => 'Role Management',
                'description' => 'Edit roles and assign permissions.',
            ],
            [
                'name' => 'role-management.delete',
                'module' => 'Role Management',
                'description' => 'Delete custom roles.',
            ],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                ['name' => $permission['name']],
                [
                    'module' => $permission['module'],
                    'description' => $permission['description'],
                ]
            );
        }

        $superAdmin = Role::where(
            'name',
            'Super Administrator'
        )->first();

        if ($superAdmin) {
            $superAdmin->permissions()->syncWithoutDetaching(
                Permission::whereIn('name', [
                    'role-management.view',
                    'role-management.create',
                    'role-management.update',
                    'role-management.delete',
                ])->pluck('id')->toArray()
            );
        }
    }
}