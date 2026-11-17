<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class ProgramManagementPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            [
                'name' => 'programs.view',
                'module' => 'Program Management',
                'description' => 'View program records',
            ],
            [
                'name' => 'programs.create',
                'module' => 'Program Management',
                'description' => 'Create new programs',
            ],
            [
                'name' => 'programs.update',
                'module' => 'Program Management',
                'description' => 'Update and manage program status',
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
            $permissionIds = Permission::whereIn(
                'name',
                [
                    'programs.view',
                    'programs.create',
                    'programs.update',
                ]
            )->pluck('id');

            $superAdmin->permissions()->syncWithoutDetaching(
                $permissionIds
            );
        }
    }
}