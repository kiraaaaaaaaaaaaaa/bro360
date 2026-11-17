<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class OutputManagementPermissionSeeder extends Seeder
{
     public function run(): void
    {
        $permissions = [
            [
                'name' => 'outputs.view',
                'module' => 'Output Management',
                'description' => 'View program outputs',
            ],
            [
                'name' => 'outputs.create',
                'module' => 'Output Management',
                'description' => 'Create program outputs',
            ],
            [
                'name' => 'outputs.update',
                'module' => 'Output Management',
                'description' => 'Update and change output status',
            ],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission['name']],
                [
                    'module' => $permission['module'],
                    'description' => $permission['description'],
                ]
            );
        }

        $superAdmin = Role::where('name', 'Super Administrator')->first();

        if ($superAdmin) {
            $permissionIds = Permission::whereIn('name', [
                'outputs.view',
                'outputs.create',
                'outputs.update',
            ])->pluck('id');

            $superAdmin->permissions()->syncWithoutDetaching($permissionIds);
        }
    }
}
