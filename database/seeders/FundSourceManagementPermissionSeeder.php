<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class FundSourceManagementPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            [
                'name' => 'fund-sources.view',
                'module' => 'Fund Source Management',
                'description' => 'View fund sources.',
            ],
            [
                'name' => 'fund-sources.create',
                'module' => 'Fund Source Management',
                'description' => 'Create fund sources.',
            ],
            [
                'name' => 'fund-sources.update',
                'module' => 'Fund Source Management',
                'description' => 'Edit and activate or deactivate fund sources.',
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
            $superAdmin->permissions()->syncWithoutDetaching(
                Permission::whereIn(
                    'name',
                    collect($permissions)->pluck('name')
                )->pluck('id')
            );
        }
    }
}