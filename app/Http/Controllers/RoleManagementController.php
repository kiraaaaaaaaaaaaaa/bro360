<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class RoleManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = Role::withCount('users')
            ->with('permissions')
            ->orderBy('name');

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $roles = $query->paginate(10)->withQueryString();

        $permissions = Permission::orderBy('module')
            ->orderBy('name')
            ->get()
            ->groupBy('module');

        return view('roles.index', compact(
            'roles',
            'permissions'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                'unique:roles,name',
            ],
            'description' => ['nullable', 'string'],
            'permission_ids' => ['nullable', 'array'],
            'permission_ids.*' => [
                'integer',
                'exists:permissions,id',
            ],
        ]);

        DB::transaction(function () use ($validated) {
            $role = Role::create([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
                'is_system' => false,
            ]);

            $role->permissions()->sync(
                $validated['permission_ids'] ?? []
            );
        });

        return redirect()
            ->route('roles.index')
            ->with('success', 'Role created successfully.');
    }

    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('roles', 'name')->ignore($role->id),
            ],
            'description' => ['nullable', 'string'],
            'permission_ids' => ['nullable', 'array'],
            'permission_ids.*' => [
                'integer',
                'exists:permissions,id',
            ],
        ]);

        DB::transaction(function () use ($validated, $role) {
            $role->update([
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
            ]);

            $role->permissions()->sync(
                $validated['permission_ids'] ?? []
            );
        });

        return redirect()
            ->route('roles.index')
            ->with('success', 'Role and permissions updated successfully.');
    }

    public function destroy(Role $role)
    {
        if ($role->is_system) {
            return back()->with(
                'error',
                'System roles cannot be deleted.'
            );
        }

        if ($role->users()->exists()) {
            return back()->with(
                'error',
                'This role cannot be deleted because users are assigned to it.'
            );
        }

        $role->permissions()->detach();
        $role->delete();

        return redirect()
            ->route('roles.index')
            ->with('success', 'Role deleted successfully.');
    }
}