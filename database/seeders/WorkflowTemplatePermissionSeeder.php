<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class WorkflowTemplatePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            [
                'name' => 'workflow-templates.view',
                'module' => 'Workflow Template Management',
                'description' => 'View workflow templates.',
            ],
            [
                'name' => 'workflow-templates.create',
                'module' => 'Workflow Template Management',
                'description' => 'Create workflow templates and stages.',
            ],
            [
                'name' => 'workflow-templates.update',
                'module' => 'Workflow Template Management',
                'description' => 'Update templates, stages, and reviewer assignments.',
            ],
            [
                'name' => 'workflow-templates.delete',
                'module' => 'Workflow Template Management',
                'description' => 'Delete unused workflow templates and stages.',
            ],
            [
                'name' => 'workflow-templates.activate',
                'module' => 'Workflow Template Management',
                'description' => 'Activate and deactivate workflow templates.',
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
                collect($permissions)->pluck('name')
            )->pluck('id');

            $superAdmin->permissions()->syncWithoutDetaching($permissionIds);
        }
    }
}