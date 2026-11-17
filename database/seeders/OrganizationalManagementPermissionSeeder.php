<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Database\Seeder;

class OrganizationalManagementPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            [
                'name' => 'organization-management.view',
                'module' => 'Organizational Management',
                'description' => 'View organizational structure records.',
            ],
            [
                'name' => 'organization-management.create',
                'module' => 'Organizational Management',
                'description' => 'Create offices, divisions, units, and positions.',
            ],
            [
                'name' => 'organization-management.update',
                'module' => 'Organizational Management',
                'description' => 'Update and activate or deactivate organizational records.',
            ],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                ['name' => $permission['name']],
                $permission
            );
        }

        $superAdmin = Role::where(
            'name',
            'Super Administrator'
        )->first();

        if ($superAdmin) {
            $permissionIds = Permission::whereIn(
                'name',
                array_column($permissions, 'name')
            )->pluck('id');

            $superAdmin->permissions()->syncWithoutDetaching(
                $permissionIds
            );
        }
    }
}