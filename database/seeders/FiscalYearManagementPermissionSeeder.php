<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class FiscalYearManagementPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            [
                'name' => 'fiscal-year-management.view',
                'module' => 'Fiscal Year Management',
                'description' => 'View fiscal year records.',
            ],
            [
                'name' => 'fiscal-year-management.create',
                'module' => 'Fiscal Year Management',
                'description' => 'Create fiscal year records.',
            ],
            [
                'name' => 'fiscal-year-management.update',
                'module' => 'Fiscal Year Management',
                'description' => 'Edit, activate, and close fiscal years.',
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

        $superAdmin = Role::where('name', 'Super Administrator')->first();

        if ($superAdmin) {
            $permissionIds = Permission::whereIn(
                'name',
                array_column($permissions, 'name')
            )->pluck('id');

            $superAdmin->permissions()->syncWithoutDetaching($permissionIds);
        }
    }
}