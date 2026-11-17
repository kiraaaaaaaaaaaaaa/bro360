<?php

namespace App\Http\Controllers;

use App\Models\WorkflowTemplate;
use App\Models\WorkflowStep;
use App\Models\WorkflowTemplateAssignee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use App\Models\User;
use App\Models\Role;
use App\Models\Position;
use App\Models\Division;
use App\Models\OrganizationalUnit;
use App\Models\Office;

class WorkflowTemplateController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Workflow Template Directory
    |--------------------------------------------------------------------------
    */

    public function index(Request $request)
{
    $query = WorkflowTemplate::withCount('steps')
        ->with([
            'creator',
            'steps.assignees',
        ])
        ->latest();

    if ($request->filled('search')) {
        $search = $request->search;

        $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        });
    }

    if ($request->filled('process_type')) {
        $query->where('process_type', $request->process_type);
    }

    if ($request->filled('status')) {
        $query->where(
            'is_active',
            $request->status === 'active'
        );
    }

    $templates = $query->paginate(10)->withQueryString();

    $offices = Office::where('is_active', true)
    ->orderBy('name')
    ->get(['id', 'name']);

    $counts = [
        'total' => WorkflowTemplate::count(),
        'active' => WorkflowTemplate::where('is_active', true)->count(),
        'inactive' => WorkflowTemplate::where('is_active', false)->count(),
    ];

    $users = User::where('is_active', true)
        ->orderBy('name')
        ->get(['id', 'name']);

    $roles = Role::orderBy('name')->get(['id', 'name']);

    $positions = Position::where('is_active', true)
        ->orderBy('title')
        ->get(['id', 'title']);

    $divisions = Division::where('is_active', true)
        ->orderBy('name')
        ->get(['id', 'name']);

    $organizationalUnits = OrganizationalUnit::where('is_active', true)
        ->orderBy('name')
        ->get(['id', 'name']);

    return view('workflow-templates.index', compact(
        'templates',
        'counts',
        'users',
        'roles',
        'positions',
        'divisions',
        'organizationalUnits',
        'offices'
        
    ));
}

    /*
    |--------------------------------------------------------------------------
    | Create Workflow Template
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:150',
            ],
            'process_type' => [
                'required',
                Rule::in(['pap', 'activity', 'task', 'budget']),
            ],
            'description' => [
                'nullable',
                'string',
            ],
        ]);

        $duplicate = WorkflowTemplate::where('name', $validated['name'])
            ->where('process_type', $validated['process_type'])
            ->exists();

        if ($duplicate) {
            return back()
                ->withInput()
                ->withErrors([
                    'name' => 'A workflow template with this name and process type already exists.',
                ]);
        }

        WorkflowTemplate::create([
            'name' => $validated['name'],
            'process_type' => $validated['process_type'],
            'description' => $validated['description'] ?? null,
            'version' => 1,
            'is_active' => false,
            'created_by' => Auth::id(),
        ]);

        return redirect()
            ->route('workflow-templates.index')
            ->with('success', 'Workflow template created successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | Update Workflow Template
    |--------------------------------------------------------------------------
    */

    public function update(Request $request, WorkflowTemplate $workflowTemplate)
    {
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:150',
            ],
            'process_type' => [
                'required',
                Rule::in(['pap', 'activity', 'task', 'budget']),
            ],
            'description' => [
                'nullable',
                'string',
            ],
        ]);

        $duplicate = WorkflowTemplate::where('name', $validated['name'])
            ->where('process_type', $validated['process_type'])
            ->where('id', '!=', $workflowTemplate->id)
            ->exists();

        if ($duplicate) {
            return back()
                ->withInput()
                ->withErrors([
                    'name' => 'A workflow template with this name and process type already exists.',
                ]);
        }

        $workflowTemplate->update($validated);

        return back()->with('success', 'Workflow template updated successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | Activate / Deactivate Workflow Template
    |--------------------------------------------------------------------------
    */

    public function toggleStatus(WorkflowTemplate $workflowTemplate)
    {
        if (!$workflowTemplate->is_active) {
            $workflowTemplate->load('steps.assignees');

            if ($workflowTemplate->steps->isEmpty()) {
                return back()->with('error', 'Add at least one workflow stage before activating this template.');
            }

            foreach ($workflowTemplate->steps as $step) {
                if ($step->assignees->isEmpty()) {
                    return back()->with(
                        'error',
                        "The stage '{$step->name}' has no reviewer assignment."
                    );
                }
            }
        }

        $workflowTemplate->update([
            'is_active' => !$workflowTemplate->is_active,
        ]);

        return back()->with(
            'success',
            $workflowTemplate->is_active
                ? 'Workflow template activated successfully.'
                : 'Workflow template deactivated successfully.'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Delete Workflow Template
    |--------------------------------------------------------------------------
    */
    
    public function destroy(WorkflowTemplate $workflowTemplate)
    {
        if ($workflowTemplate->is_active) {
    return back()->with('error', 'Deactivate the workflow template before deleting it.');
}
        // Prevent deletion if workflow transactions already use this template.
        if (
            Schema::hasTable('workflow_instances') &&
            DB::table('workflow_instances')
                ->where('workflow_template_id', $workflowTemplate->id)
                ->exists()
        ) {
            return back()->with(
                'error',
                'This template cannot be deleted because it has already been used in workflow transactions.'
            );
        }

        $workflowTemplate->delete();

        return back()->with('success', 'Workflow template deleted successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | Add Workflow Stage
    |--------------------------------------------------------------------------
    */

    public function storeStep(Request $request, WorkflowTemplate $workflowTemplate)
    {
        if ($workflowTemplate->is_active) {
            return back()->with('error', 'Deactivate the workflow template before modifying its stages.');
        }

        $validated = $request->validate([
            'step_name' => ['required', 'string', 'max:150'],
            'action_type' => [
                'required',
                Rule::in(['review', 'approval']),
            ],
            'completion_rule' => [
                'required',
                Rule::in(['any', 'all']),
            ],
            'due_days' => ['nullable', 'integer', 'min:1', 'max:365'],
            'allow_return' => ['nullable', 'boolean'],
            'allow_reject' => ['nullable', 'boolean'],
            'allow_not_applicable' => ['nullable', 'boolean'],
        ]);

        $nextOrder = ((int) $workflowTemplate->steps()->max('step_order')) + 1;

        $workflowTemplate->steps()->create([
            'step_order' => $nextOrder,
            'step_name' => $validated['step_name'],
            'action_type' => $validated['action_type'],
            'completion_rule' => $validated['completion_rule'],
            'due_days' => $validated['due_days'] ?? null,
            'allow_return' => $request->boolean('allow_return'),
            'allow_reject' => $request->boolean('allow_reject'),
            'allow_not_applicable' => $request->boolean('allow_not_applicable'),
        ]);

        return back()->with('success', 'Workflow stage added successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | Update Workflow Stage
    |--------------------------------------------------------------------------
    */

    public function updateStep(
        Request $request,
        WorkflowTemplate $workflowTemplate,
        WorkflowStep $workflowStep
    ) {
        if ($workflowTemplate->is_active) {
            return back()->with('error', 'Deactivate the workflow template before modifying its stages.');
        }

        abort_unless(
            $workflowStep->workflow_template_id === $workflowTemplate->id,
            404
        );

        $validated = $request->validate([
        'step_name' => ['required', 'string', 'max:255'],
        'role_id' => ['nullable', 'exists:roles,id'],
        'action_type' => ['required', 'in:review,approval'],
        'completion_rule' => ['required', 'in:all,any'],
        'due_days' => ['nullable', 'integer', 'min:1'],
        'allow_return' => ['nullable', 'boolean'],
        'allow_reject' => ['nullable', 'boolean'],
        'allow_not_applicable' => ['nullable', 'boolean'],
    ]);

        $workflowStep->update([
            'step_name' => $validated['step_name'],
            'role_id' => $validated['role_id'] ?? null,
            'action_type' => $validated['action_type'],
            'completion_rule' => $validated['completion_rule'],
            'due_days' => $validated['due_days'] ?? null,
            'allow_return' => $request->boolean('allow_return'),
            'allow_reject' => $request->boolean('allow_reject'),
            'allow_not_applicable' => $request->boolean('allow_not_applicable'),
        ]);

        return back()->with('success', 'Workflow stage updated successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | Delete Workflow Stage
    |--------------------------------------------------------------------------
    */

    public function destroyStep(
        WorkflowTemplate $workflowTemplate,
        WorkflowStep $workflowStep
    ) {
        if ($workflowTemplate->is_active) {
            return back()->with('error', 'Deactivate the workflow template before deleting its stages.');
        }

        abort_unless(
            $workflowStep->workflow_template_id === $workflowTemplate->id,
            404
        );

        DB::transaction(function () use ($workflowTemplate, $workflowStep) {
            $workflowStep->delete();

            $remainingSteps = $workflowTemplate->steps()
                ->orderBy('step_order')
                ->get();

            foreach ($remainingSteps as $index => $step) {
                $step->update(['step_order' => $index + 1]);
            }
        });

        return back()->with('success', 'Workflow stage deleted successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | Reorder Workflow Stages
    |--------------------------------------------------------------------------
    */

    public function reorderSteps(Request $request, WorkflowTemplate $workflowTemplate)
{
    if ($workflowTemplate->is_active) {
        return back()->with('error', 'Deactivate the workflow template before reordering its stages.');
    }

    $validated = $request->validate([
        'steps' => ['required', 'array', 'min:1'],
        'steps.*' => ['required', 'integer', 'distinct'],
    ]);

    $existingIds = $workflowTemplate->steps()
        ->pluck('id')
        ->sort()
        ->values()
        ->all();

    $submittedIds = collect($validated['steps'])
        ->sort()
        ->values()
        ->all();

    if ($existingIds !== $submittedIds) {
        return back()->with('error', 'Invalid stage order submitted.');
    }

    DB::transaction(function () use ($validated, $workflowTemplate) {

        $maxOrder = (int) $workflowTemplate->steps()->max('step_order');
        $offset = $maxOrder + count($validated['steps']) + 1;

        // Move all stages to temporary positions first.
        foreach ($validated['steps'] as $index => $stepId) {
            WorkflowStep::whereKey($stepId)->update([
                'step_order' => $offset + $index + 1,
            ]);
        }

        // Apply the new order.
        foreach ($validated['steps'] as $index => $stepId) {
            WorkflowStep::whereKey($stepId)->update([
                'step_order' => $index + 1,
            ]);
        }
    });

    return back()->with('success', 'Workflow stages reordered successfully.');
}

    /*
    |--------------------------------------------------------------------------
    | Add Reviewer Assignment
    |--------------------------------------------------------------------------
    */

public function storeAssignee(Request $request, string $template, string $step)
{
    $validated = $request->validate([
        'assignment_type' => [
            'required',
            Rule::in([
                'office',
                'user',
                'role',
                'position',
                'division',
                'organizational_unit',
            ]),
        ],
        'assignment_id' => ['required', 'integer'],
    ]);

    // Find the workflow template and its corresponding stage
    $workflowTemplate = WorkflowTemplate::findOrFail($template);

    $workflowStep = $workflowTemplate->steps()->findOrFail($step);

    // Check if the reviewer is already assigned
    $existingAssignee = $workflowStep->assignees()
        ->where('assignment_type', $validated['assignment_type'])
        ->where('assignment_id', $validated['assignment_id'])
        ->first();

    if (!$existingAssignee) {

        // Get the next review order within this stage
        $nextReviewOrder = (int) $workflowStep->assignees()
            ->max('review_order') + 1;

        $workflowStep->assignees()->create([
            'assignment_type' => $validated['assignment_type'],
            'assignment_id' => $validated['assignment_id'],
            'review_order' => $nextReviewOrder,
        ]);
    }

    return back()->with(
        'success',
        'Reviewer assignment saved successfully.'
    );
}

    /*
    |--------------------------------------------------------------------------
    | Remove Reviewer Assignment
    |--------------------------------------------------------------------------
    */

    public function destroyAssignee(
        WorkflowTemplate $workflowTemplate,
        WorkflowStep $workflowStep,
        WorkflowTemplateAssignee $assignee
    ) {
        if ($workflowTemplate->is_active) {
            return back()->with('error', 'Deactivate the workflow template before modifying reviewer assignments.');
        }

        abort_unless(
            $workflowStep->workflow_template_id === $workflowTemplate->id &&
            $assignee->workflow_template_step_id === $workflowStep->id,
            404
        );

        $assignee->delete();

        return back()->with('success', 'Reviewer assignment removed successfully.');
    }
}