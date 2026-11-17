<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::before(function (User $user, string $ability) {
            if ($user->roles()->where('name', 'Super Administrator')->exists()) {
                return true;
            }

            return null;
        });

        Gate::define(
            'user-management.view',
            fn(User $user) =>
            $user->hasPermissionTo('user-management.view')
        );

        Gate::define(
            'user-management.create',
            fn(User $user) =>
            $user->hasPermissionTo('user-management.create')
        );

        Gate::define(
            'user-management.update',
            fn(User $user) =>
            $user->hasPermissionTo('user-management.update')
        );

        Gate::define(
            'user-management.status',
            fn(User $user) =>
            $user->hasPermissionTo('user-management.status')
        );

        Gate::define(
            'role-management.view',
            fn(User $user) =>
            $user->hasPermissionTo('role-management.view')
        );

        Gate::define(
            'role-management.create',
            fn(User $user) =>
            $user->hasPermissionTo('role-management.create')
        );

        Gate::define(
            'role-management.update',
            fn(User $user) =>
            $user->hasPermissionTo('role-management.update')
        );

        Gate::define(
            'role-management.delete',
            fn(User $user) =>
            $user->hasPermissionTo('role-management.delete')
        );

        Gate::define(
            'organization-management.view',
            fn(User $user) =>
            $user->hasPermissionTo('organization-management.view')
        );

        Gate::define(
            'organization-management.create',
            fn(User $user) =>
            $user->hasPermissionTo('organization-management.create')
        );

        Gate::define(
            'organization-management.update',
            fn(User $user) =>
            $user->hasPermissionTo('organization-management.update')
        );

        Gate::define(
            'fiscal-year-management.view',
            fn(User $user) =>
            $user->hasPermissionTo('fiscal-year-management.view')
        );

        Gate::define(
            'fiscal-year-management.create',
            fn(User $user) =>
            $user->hasPermissionTo('fiscal-year-management.create')
        );

        Gate::define(
            'fiscal-year-management.update',
            fn(User $user) =>
            $user->hasPermissionTo('fiscal-year-management.update')
        );

        Gate::define(
            'workflow-templates.view',
            fn(User $user) =>
            $user->hasPermissionTo('workflow-templates.view')
        );

        Gate::define(
            'workflow-templates.create',
            fn(User $user) =>
            $user->hasPermissionTo('workflow-templates.create')
        );

        Gate::define(
            'workflow-templates.update',
            fn(User $user) =>
            $user->hasPermissionTo('workflow-templates.update')
        );

        Gate::define(
            'workflow-templates.delete',
            fn(User $user) =>
            $user->hasPermissionTo('workflow-templates.delete')
        );

        Gate::define(
            'workflow-templates.activate',
            fn(User $user) =>
            $user->hasPermissionTo('workflow-templates.activate')
        );
    }
}
