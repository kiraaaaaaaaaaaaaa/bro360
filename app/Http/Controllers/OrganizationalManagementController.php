<?php

namespace App\Http\Controllers;

use App\Models\Office;
use App\Models\Division;
use App\Models\OrganizationalUnit;
use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class OrganizationalManagementController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $status = $request->input('status', 'all');

        $offices = Office::withCount('divisions')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->when($status !== 'all', function ($query) use ($status) {
                $query->where('is_active', $status === 'active');
            })
            ->orderBy('name')
            ->paginate(10, ['*'], 'offices_page')
            ->withQueryString();

        $divisions = Division::with('office')
            ->withCount('organizationalUnits')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->when($status !== 'all', function ($query) use ($status) {
                $query->where('is_active', $status === 'active');
            })
            ->orderBy('name')
            ->paginate(10, ['*'], 'divisions_page')
            ->withQueryString();

        $units = OrganizationalUnit::with([
                'division.office',
                'parent',
            ])
            ->withCount('children')
            ->when($search, function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->when($status !== 'all', function ($query) use ($status) {
                $query->where('is_active', $status === 'active');
            })
            ->orderBy('name')
            ->paginate(10, ['*'], 'units_page')
            ->withQueryString();

        $positions = Position::withCount('users')
            ->when($search, function ($query) use ($search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('salary_grade', 'like', "%{$search}%");
            })
            ->when($status !== 'all', function ($query) use ($status) {
                $query->where('is_active', $status === 'active');
            })
            ->orderBy('title')
            ->paginate(10, ['*'], 'positions_page')
            ->withQueryString();

        return view('organizational-management.index', [
            'offices' => $offices,
            'divisions' => $divisions,
            'units' => $units,
            'positions' => $positions,
            'officeOptions' => Office::where('is_active', true)
                ->orderBy('name')->get(),
            'divisionOptions' => Division::where('is_active', true)
                ->orderBy('name')->get(),
            'unitOptions' => OrganizationalUnit::where('is_active', true)
                ->orderBy('name')->get(),
            'counts' => [
                'offices' => Office::count(),
                'divisions' => Division::count(),
                'units' => OrganizationalUnit::count(),
                'positions' => Position::count(),
            ],
            'activeCounts' => [
                'offices' => Office::where('is_active', true)->count(),
                'divisions' => Division::where('is_active', true)->count(),
                'units' => OrganizationalUnit::where('is_active', true)->count(),
                'positions' => Position::where('is_active', true)->count(),
            ],
            'search' => $search,
            'status' => $status,
        ]);
    }

    /* =====================================================
       OFFICES
    ===================================================== */

    public function storeOffice(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:180'],
            'code' => ['nullable', 'string', 'max:50', 'unique:offices,code'],
            'description' => ['nullable', 'string'],
        ]);

        Office::create($validated);

        return back()->with('success', 'Office added successfully.');
    }

    public function updateOffice(Request $request, Office $office)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:180'],
            'code' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('offices', 'code')->ignore($office->id),
            ],
            'description' => ['nullable', 'string'],
        ]);

        $office->update($validated);

        return back()->with('success', 'Office updated successfully.');
    }

    /* =====================================================
       DIVISIONS
    ===================================================== */

    public function storeDivision(Request $request)
    {
        $validated = $request->validate([
            'office_id' => ['nullable', 'exists:offices,id'],
            'name' => ['required', 'string', 'max:180'],
            'code' => ['nullable', 'string', 'max:50', 'unique:divisions,code'],
            'description' => ['nullable', 'string'],
        ]);

        Division::create($validated);

        return back()->with('success', 'Division added successfully.');
    }

    public function updateDivision(Request $request, Division $division)
    {
        $validated = $request->validate([
            'office_id' => ['nullable', 'exists:offices,id'],
            'name' => ['required', 'string', 'max:180'],
            'code' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('divisions', 'code')->ignore($division->id),
            ],
            'description' => ['nullable', 'string'],
        ]);

        $division->update($validated);

        return back()->with('success', 'Division updated successfully.');
    }

    /* =====================================================
       ORGANIZATIONAL UNITS
    ===================================================== */

    public function storeUnit(Request $request)
    {
        $validated = $request->validate([
            'division_id' => ['nullable', 'exists:divisions,id'],
            'parent_id' => ['nullable', 'exists:organizational_units,id'],
            'name' => ['required', 'string', 'max:180'],
            'unit_type' => ['nullable', 'string', 'max:80'],
            'description' => ['nullable', 'string'],
        ]);

        if (!empty($validated['parent_id'])) {
            $parent = OrganizationalUnit::findOrFail(
                $validated['parent_id']
            );

            if (
                !empty($validated['division_id']) &&
                $parent->division_id &&
                $parent->division_id != $validated['division_id']
            ) {
                return back()
                    ->withErrors([
                        'parent_id' => 'The parent unit must belong to the selected division.',
                    ])
                    ->withInput();
            }
        }

        OrganizationalUnit::create($validated);

        return back()->with('success', 'Organizational unit added successfully.');
    }

    public function updateUnit(Request $request, OrganizationalUnit $unit)
    {
        $validated = $request->validate([
            'division_id' => ['nullable', 'exists:divisions,id'],
            'parent_id' => [
                'nullable',
                'exists:organizational_units,id',
                Rule::notIn([$unit->id]),
            ],
            'name' => ['required', 'string', 'max:180'],
            'unit_type' => ['nullable', 'string', 'max:80'],
            'description' => ['nullable', 'string'],
        ]);

        if (!empty($validated['parent_id'])) {
            $parent = OrganizationalUnit::findOrFail(
                $validated['parent_id']
            );

            if (
                !empty($validated['division_id']) &&
                $parent->division_id &&
                $parent->division_id != $validated['division_id']
            ) {
                return back()
                    ->withErrors([
                        'parent_id' => 'The parent unit must belong to the selected division.',
                    ])
                    ->withInput();
            }
        }

        $unit->update($validated);

        return back()->with('success', 'Organizational unit updated successfully.');
    }

    /* =====================================================
       POSITIONS
    ===================================================== */

    public function storePosition(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'salary_grade' => ['nullable', 'string', 'max:30'],
        ]);

        Position::create($validated);

        return back()->with('success', 'Position added successfully.');
    }

    public function updatePosition(Request $request, Position $position)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'salary_grade' => ['nullable', 'string', 'max:30'],
        ]);

        $position->update($validated);

        return back()->with('success', 'Position updated successfully.');
    }

    /* =====================================================
       ACTIVATE / DEACTIVATE
    ===================================================== */

    public function toggleStatus(Request $request, string $type, int $id)
    {
        $models = [
            'office' => Office::class,
            'division' => Division::class,
            'unit' => OrganizationalUnit::class,
            'position' => Position::class,
        ];

        abort_unless(isset($models[$type]), 404);

        $record = $models[$type]::findOrFail($id);

        $record->update([
            'is_active' => !$record->is_active,
        ]);

        return back()->with(
            'success',
            ucfirst($type) . ' status updated successfully.'
        );
    }
}