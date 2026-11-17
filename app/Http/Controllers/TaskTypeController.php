<?php

namespace App\Http\Controllers;


use App\Models\TaskType;
use App\Models\WorkflowTemplate;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TaskTypeController extends Controller
{
    public function index()
{
    $taskTypes = TaskType::with('workflowTemplate')
        ->orderBy('title')
        ->paginate(10);

    $workflowTemplates = WorkflowTemplate::where('process_type', 'task')
        ->orderBy('name')
        ->get();

    return view('task-types.index', compact(
        'taskTypes',
        'workflowTemplates'
    ));
}

  

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255', 'unique:task_types,title'],
            'description' => ['nullable', 'string'],
            'category' => ['nullable', 'string', 'max:255'],
            'workflow_template_id' => [
                'required',
                'exists:workflow_templates,id',
            ],
        ]);

        $validated['is_active'] = true;

        TaskType::create($validated);

        return redirect()
            ->route('task-types.index')
            ->with('success', 'Task type created successfully.');
    }

  

    public function update(Request $request, TaskType $taskType)
    {
        $validated = $request->validate([
            'title' => [
                'required',
                'string',
                'max:255',
                Rule::unique('task_types', 'title')->ignore($taskType->id),
            ],
            'description' => ['nullable', 'string'],
            'category' => ['nullable', 'string', 'max:255'],
            'workflow_template_id' => [
                'required',
                'exists:workflow_templates,id',
            ],
        ]);

        $taskType->update($validated);

        return redirect()
            ->route('task-types.index')
            ->with('success', 'Task type updated successfully.');
    }

    public function destroy(TaskType $taskType)
    {
        $taskType->update([
            'is_active' => false,
        ]);

        return redirect()
            ->route('task-types.index')
            ->with('success', 'Task type deactivated successfully.');
    }
}

