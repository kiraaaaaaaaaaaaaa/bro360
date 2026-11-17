<?php

namespace App\Http\Controllers;


use App\Models\Program;
use App\Models\OutputCategory;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OutputManagementController extends Controller
{
    public function index(Request $request, Program $program)
    {
        $outputs = $program->outputs()
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('code', 'like', "%{$search}%")
                      ->orWhere('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($request->status !== null && $request->status !== '', function ($query) use ($request) {
                $query->where('is_active', $request->status);
            })
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        $totalOutputs = $program->outputs()->count();
        $activeOutputs = $program->outputs()->where('is_active', true)->count();
        $inactiveOutputs = $program->outputs()->where('is_active', false)->count();

        return view('outputs.index', compact(
            'program',
            'outputs',
            'totalOutputs',
            'activeOutputs',
            'inactiveOutputs'
        ));
    }

    public function store(Request $request, Program $program)
    {
        $validated = $request->validate([
            'code' => [
                'nullable',
                'string',
                'max:60',
                Rule::unique('output_categories', 'code'),
            ],
            'name' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string'],
        ]);

        $program->outputs()->create([
            ...$validated,
            'is_active' => true,
        ]);

        return redirect()
            ->route('outputs.index', $program)
            ->with('success', 'Output created successfully.');
    }

    public function update(Request $request, Program $program, OutputCategory $output)
    {
        abort_unless($output->program_id === $program->id, 404);

        $validated = $request->validate([
            'code' => [
                'nullable',
                'string',
                'max:60',
                Rule::unique('output_categories', 'code')->ignore($output->id),
            ],
            'name' => ['required', 'string', 'max:180'],
            'description' => ['nullable', 'string'],
        ]);

        $output->update($validated);

        return redirect()
            ->route('outputs.index', $program)
            ->with('success', 'Output updated successfully.');
    }

    public function toggleStatus(Program $program, OutputCategory $output)
    {
        abort_unless($output->program_id === $program->id, 404);

        $output->update([
            'is_active' => !$output->is_active,
        ]);

        return redirect()
            ->route('outputs.index', $program)
            ->with('success', 'Output status updated successfully.');
    }
}
