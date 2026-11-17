@extends('layouts.app')

@section('title', 'Create Task')
@section('page-title', 'Create Task')
@section('page-description', 'Create, assign, and configure a task under a PAP.')

@section('content')

<div class="page-container task-form-page">

    {{-- HEADER --}}
    <div class="task-form-header">
        <div>
            <span class="task-form-eyebrow">TASK MANAGEMENT</span>
            <h1>Create New Task</h1>
            <p>Define the task details, responsible personnel, schedule, and reviewer workflow.</p>
        </div>

        <a href="{{ route('tasks.index') }}" class="task-form-back">
            Back to Tasks
        </a>
    </div>

    {{-- VALIDATION ERRORS --}}
    @if ($errors->any())
    <div class="task-form-alert">
        <strong>Please review the following:</strong>
        <ul>
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('tasks.store') }}" method="POST">
        @csrf

        {{-- SECTION 01 --}}
        <section class="task-form-section">

            <div class="task-form-section-heading">
                <span class="task-form-step">01</span>
                <div>
                    <h3>Task Information</h3>
                    <p>Identify the PAP, activity, and task details.</p>
                </div>
            </div>

            <div class="task-form-grid">

                <div class="task-form-field">
                    <label for="pap_id">Parent PAP <span>*</span></label>

                    <select name="pap_id" id="pap_id" required>
                        <option value="">Select PAP</option>

                        @foreach($paps as $pap)
                        <option value="{{ $pap->id }}" {{ old('pap_id', $selectedPapId ?? '') == $pap->id ? 'selected' : '' }}>
                            {{ $pap->pap_code }} - {{ $pap->name }}
                        </option>
                        @endforeach
                    </select>

                    @error('pap_id')
                    <small class="task-form-error">{{ $message }}</small>
                    @enderror
                </div>

                <div class="task-form-field">
                    <label for="pap_activity_id">Related Activity</label>

                    <select name="pap_activity_id" id="pap_activity_id">
                        <option value="">General PAP Task (No Activity)</option>
                    </select>

                    <small>Optional. Select an activity if this task contributes to its implementation progress.</small>

                    @error('pap_activity_id')
                    <small class="task-form-error">{{ $message }}</small>
                    @enderror
                </div>

                <div class="task-form-field task-form-full">
                    <label for="task_type_id">Task Type <span>*</span></label>

                    <select name="task_type_id" id="task_type_id" required>
                        <option value="">Select Task Type</option>

                        @foreach($taskTypes as $taskType)
                        <option value="{{ $taskType->id }}" data-workflow-name="{{ $taskType->workflowTemplate?->name }}" {{ old('task_type_id') == $taskType->id ? 'selected' : '' }}>
                            {{ $taskType->title }}
                        </option>
                        @endforeach
                    </select>

                    @error('task_type_id')
                    <span class="field-error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="task-form-field task-form-full">
                    <label for="description">Task Description</label>

                    <textarea name="description" id="description" rows="4" placeholder="Describe the expected output, scope, or deliverables...">{{ old('description') }}</textarea>
                </div>

                <div class="task-form-field">
                    <label for="priority">Priority <span>*</span></label>

                    <select name="priority" id="priority" required>
                        @foreach(['Low', 'Normal', 'High', 'Urgent'] as $priority)
                        <option value="{{ $priority }}" {{ old('priority', 'Normal') == $priority ? 'selected' : '' }}>
                            {{ $priority }}
                        </option>
                        @endforeach
                    </select>
                </div>

            </div>
        </section>

        {{-- SECTION 02 --}}
        <section class="task-form-section">

            <div class="task-form-section-heading">
                <span class="task-form-step">02</span>
                <div>
                    <h3>Task Assignment</h3>
                    <p>Identify the personnel responsible for completing the task.</p>
                </div>
            </div>

            <div class="task-form-grid">

                <div class="task-form-field task-form-full">
                    <label for="assigned_users">Assigned Personnel <span>*</span></label>

                    <select name="assigned_users[]" id="assigned_users" multiple required>
                        @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ in_array($user->id, old('assigned_users', [])) ? 'selected' : '' }}>
                            {{ $user->name }}
                        </option>
                        @endforeach
                    </select>

                    <small>Select all personnel who will work on this task. Hold Ctrl to select multiple personnel.</small>

                    @error('assigned_users')
                    <small class="task-form-error">{{ $message }}</small>
                    @enderror
                </div>

                <div class="task-form-field">
                    <label for="primary_assignee_id">Primary Assignee <span>*</span></label>

                    <select name="primary_assignee_id" id="primary_assignee_id" required>
                        <option value="">Select Primary Assignee</option>

                        @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ old('primary_assignee_id') == $user->id ? 'selected' : '' }}>
                            {{ $user->name }}
                        </option>
                        @endforeach
                    </select>

                    <small>The primary assignee must also be included in the assigned personnel.</small>

                    @error('primary_assignee_id')
                    <small class="task-form-error">{{ $message }}</small>
                    @enderror
                </div>

            </div>
        </section>

        {{-- SECTION 03 --}}
        <section class="task-form-section">

            <div class="task-form-section-heading">
                <span class="task-form-step">03</span>
                <div>
                    <h3>Implementation Schedule</h3>
                    <p>Set the expected task implementation period.</p>
                </div>
            </div>

            <div class="task-form-grid">

                <div class="task-form-field">
                    <label for="start_date">Start Date</label>

                    <input type="date" name="start_date" id="start_date" value="{{ old('start_date') }}">
                </div>

                <div class="task-form-field">
                    <label for="due_date">Due Date</label>

                    <input type="date" name="due_date" id="due_date" value="{{ old('due_date') }}">
                </div>

            </div>
        </section>

        {{-- SECTION 04 --}}
        <section class="task-form-section">

            <div class="task-form-section-heading">
                <span class="task-form-step">04</span>
                <div>
                    <h3>Reviewer Workflow</h3>
                    <p>Choose the approval or review sequence for this task.</p>
                </div>
            </div>

            <div class="task-form-grid">

                <div class="task-form-field task-form-full">
                    <label for="workflow_template_display">Assigned Workflow</label>

                    <input type="text" id="workflow_template_display" value="" placeholder="Workflow will be assigned automatically" readonly>

                    <small class="form-help">
                        The workflow is automatically determined by the selected task type.
                    </small>
                </div>

            </div>
        </section>

        {{-- FORM ACTIONS --}}
        <div class="task-form-actions">
            <a href="{{ route('tasks.index') }}" class="task-form-cancel">
                Cancel
            </a>

            <button type="submit" class="task-form-submit">
                Create Task
            </button>
        </div>

    </form>
</div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const papSelect = document.getElementById('pap_id');
        const activitySelect = document.getElementById('pap_activity_id');

        const activitiesByPap = @json(
            $paps -> mapWithKeys(function($pap) {
                return [
                    $pap->id => $pap->activities->map(function($activity) {
                        return [
                            'id' => $activity->id
                            , 'title' => $activity->title
                        , ];
                    })->values()
                ];
            })
        );

        const selectedActivityId = @json(old('pap_activity_id', $selectedActivityId ?? ''));

        function updateActivities() {
            const papId = papSelect.value;
            const activities = activitiesByPap[papId] || [];

            activitySelect.innerHTML = '';

            const defaultOption = document.createElement('option');
            defaultOption.value = '';
            defaultOption.textContent = 'General PAP Task (No Activity)';
            activitySelect.appendChild(defaultOption);

            activities.forEach(function(activity) {
                const option = document.createElement('option');
                option.value = activity.id;
                option.textContent = activity.title;

                if (String(activity.id) === String(selectedActivityId)) {
                    option.selected = true;
                }

                activitySelect.appendChild(option);
            });
        }

        papSelect.addEventListener('change', function() {
            activitySelect.value = '';
            updateActivities();
        });

        updateActivities();
    });

</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const taskTypeSelect = document.getElementById('task_type_id');
        const workflowDisplay = document.getElementById('workflow_template_display');

        function updateWorkflow() {
            const selectedOption = taskTypeSelect.options[taskTypeSelect.selectedIndex];

            workflowDisplay.value = selectedOption ?
                selectedOption.dataset.workflowName || '' :
                '';
        }

        taskTypeSelect.addEventListener('change', updateWorkflow);

        // Display the workflow when the form loads,
        // including when validation errors cause the form to reload.
        updateWorkflow();
    });

</script>
@endpush
