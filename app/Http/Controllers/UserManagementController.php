<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\Office;
use App\Models\Division;
use App\Models\OrganizationalUnit;
use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with([
            'office',
            'division',
            'organizationalUnit',
            'position',
            'roles',
        ]);

        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('employee_no', 'like', "%{$search}%");
            });
        }

        if ($request->filled('division_id')) {
            $query->where('division_id', $request->division_id);
        }

        if ($request->filled('role_id')) {
            $query->whereHas('roles', function ($q) use ($request) {
                $q->where('roles.id', $request->role_id);
            });
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $users = $query->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        $totalUsers = User::count();
        $activeUsers = User::where('is_active', true)->count();
        $inactiveUsers = User::where('is_active', false)->count();

        $roles = Role::orderBy('name')->get();

        $offices = Office::where('is_active', true)
            ->orderBy('name')->get();

        $divisions = Division::where('is_active', true)
            ->orderBy('name')->get();

        $organizationalUnits = OrganizationalUnit::where('is_active', true)
            ->orderBy('name')->get();

        $positions = Position::where('is_active', true)
            ->orderBy('title')->get();

        return view('users.index', compact(
            'users',
            'roles',
            'offices',
            'divisions',
            'organizationalUnits',
            'positions',
            'totalUsers',
            'activeUsers',
            'inactiveUsers'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:180',
            'email' => 'required|email|max:190|unique:users,email',
            'employee_no' => 'nullable|string|max:80|unique:users,employee_no',
            'password' => 'required|string|min:8|confirmed',

            'office_id' => 'nullable|exists:offices,id',
            'division_id' => 'nullable|exists:divisions,id',
            'organizational_unit_id' => 'nullable|exists:organizational_units,id',
            'position_id' => 'nullable|exists:positions,id',

            'phone' => 'nullable|string|max:40',
            'role_ids' => 'required|array|min:1',
            'role_ids.*' => 'required|exists:roles,id',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'employee_no' => $validated['employee_no'] ?? null,
            'password' => Hash::make($validated['password']),
            'office_id' => $validated['office_id'] ?? null,
            'division_id' => $validated['division_id'] ?? null,
            'organizational_unit_id' => $validated['organizational_unit_id'] ?? null,
            'position_id' => $validated['position_id'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'is_active' => true,
        ]);

        $user->roles()->sync($validated['role_ids']);

        return redirect()
            ->route('users.index')
            ->with('success', 'User account successfully created.');
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:180',
            'email' => [
                'required',
                'email',
                'max:190',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'employee_no' => [
                'nullable',
                'string',
                'max:80',
                Rule::unique('users', 'employee_no')->ignore($user->id),
            ],
            'password' => 'nullable|string|min:8|confirmed',

            'office_id' => 'nullable|exists:offices,id',
            'division_id' => 'nullable|exists:divisions,id',
            'organizational_unit_id' => 'nullable|exists:organizational_units,id',
            'position_id' => 'nullable|exists:positions,id',

            'phone' => 'nullable|string|max:40',
            'role_ids' => 'required|array|min:1',
            'role_ids.*' => 'required|exists:roles,id',
        ]);


        $user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'employee_no' => $validated['employee_no'] ?? null,
            'office_id' => $validated['office_id'] ?? null,
            'division_id' => $validated['division_id'] ?? null,
            'organizational_unit_id' => $validated['organizational_unit_id'] ?? null,
            'position_id' => $validated['position_id'] ?? null,
            'phone' => $validated['phone'] ?? null,
        ]);

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        $user->roles()->sync($validated['role_ids']);

        return redirect()
            ->route('users.index')
            ->with('success', 'User information successfully updated.');
    }

    public function toggleStatus(User $user)
    {
        if ($user->id === Auth::id()) {
            return back()->with(
                'error',
                'You cannot deactivate your own account.'
            );
        }

        $user->is_active = !$user->is_active;
        $user->save();

        return back()->with(
            'success',
            $user->is_active
                ? 'User account activated.'
                : 'User account deactivated.'
        );
    }
}