<?php

namespace App\Http\Controllers;

use App\Models\Program;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProgramManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = Program::query();

        // Search
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where(
                'is_active',
                $request->status === 'active'
            );
        }

        $programs = $query
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        $counts = [
            'total' => Program::count(),
            'active' => Program::where('is_active', true)->count(),
            'inactive' => Program::where('is_active', false)->count(),
        ];

        return view('programs.index', compact(
            'programs',
            'counts'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => [
                'nullable',
                'string',
                'max:60',
                'unique:programs,code',
            ],
            'name' => [
                'required',
                'string',
                'max:180',
            ],
            'description' => [
                'nullable',
                'string',
            ],
        ]);

        $validated['is_active'] = true;

        Program::create($validated);

        return redirect()
            ->route('programs.index')
            ->with('success', 'Program successfully added.');
    }

    public function update(Request $request, Program $program)
    {
        $validated = $request->validate([
            'code' => [
                'nullable',
                'string',
                'max:60',
                Rule::unique('programs', 'code')->ignore($program->id),
            ],
            'name' => [
                'required',
                'string',
                'max:180',
            ],
            'description' => [
                'nullable',
                'string',
            ],
        ]);

        $program->update($validated);

        return redirect()
            ->route('programs.index')
            ->with('success', 'Program successfully updated.');
    }

    public function toggleStatus(Program $program)
    {
        $program->update([
            'is_active' => !$program->is_active,
        ]);

        $message = $program->is_active
            ? 'Program successfully activated.'
            : 'Program successfully deactivated.';

        return redirect()
            ->route('programs.index')
            ->with('success', $message);
    }
}