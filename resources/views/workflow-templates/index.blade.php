@extends('layouts.app')

@section('content')

<div class="workflow-page">

    {{-- PAGE HEADER --}}
    <div class="workflow-header">
        <div>
            <div class="workflow-breadcrumb">
                System Administration <span>/</span> Workflow Templates
            </div>

            <h1>Workflow Template Management</h1>
            <p>Configure approval processes, review stages, and reviewer assignments.</p>
        </div>

        @can('workflow-templates.create')
            <button type="button"
                    class="workflow-btn workflow-btn-primary"
                    onclick="openWorkflowModal()">
                <span>＋</span> Create Workflow
            </button>
        @endcan
    </div>


    {{-- ALERT MESSAGES --}}
    @if(session('success'))
        <div class="workflow-alert workflow-alert-success">
            <span>✓</span>
            {{ session('success') }}
            <button type="button" onclick="this.parentElement.remove()">×</button>
        </div>
    @endif

    @if(session('error'))
        <div class="workflow-alert workflow-alert-error">
            <span>!</span>
            {{ session('error') }}
            <button type="button" onclick="this.parentElement.remove()">×</button>
        </div>
    @endif

    @if($errors->any())
        <div class="workflow-alert workflow-alert-error">
            <div>
                <strong>Please check the following:</strong>
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            <button type="button" onclick="this.parentElement.remove()">×</button>
        </div>
    @endif


    {{-- SUMMARY CARDS --}}
    <div class="workflow-summary-grid">

        <div class="workflow-summary-card">
            <div class="workflow-summary-icon workflow-icon-total">▤</div>
            <div>
                <span>Total Templates</span>
                <strong>{{ $counts['total'] }}</strong>
            </div>
        </div>

        <div class="workflow-summary-card">
            <div class="workflow-summary-icon workflow-icon-active">✓</div>
            <div>
                <span>Active Templates</span>
                <strong>{{ $counts['active'] }}</strong>
            </div>
        </div>

        <div class="workflow-summary-card">
            <div class="workflow-summary-icon workflow-icon-inactive">◷</div>
            <div>
                <span>Inactive Templates</span>
                <strong>{{ $counts['inactive'] }}</strong>
            </div>
        </div>

    </div>


    {{-- TEMPLATE DIRECTORY --}}
    <div class="workflow-panel">

        <div class="workflow-panel-header">
            <div>
                <h2>Workflow Templates</h2>
                <p>Manage your organization's approval and review processes.</p>
            </div>
        </div>


        {{-- SEARCH AND FILTERS --}}
        <form method="GET"
              action="{{ route('workflow-templates.index') }}"
              class="workflow-toolbar">

            <div class="workflow-search">
                <span>⌕</span>
                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Search workflow templates...">
            </div>

            <select name="process_type" class="workflow-filter">
                <option value="">All Process Types</option>
                <option value="pap" @selected(request('process_type') === 'pap')>PAP</option>
                <option value="activity" @selected(request('process_type') === 'activity')>Activity</option>
                <option value="task" @selected(request('process_type') === 'task')>Task</option>
                <option value="budget" @selected(request('process_type') === 'budget')>Budget</option>
            </select>

            <select name="status" class="workflow-filter">
                <option value="">All Statuses</option>
                <option value="active" @selected(request('status') === 'active')>Active</option>
                <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
            </select>

            <button type="submit" class="workflow-btn workflow-btn-secondary">
                Filter
            </button>

            @if(request()->hasAny(['search', 'process_type', 'status']))
                <a href="{{ route('workflow-templates.index') }}"
                   class="workflow-clear-filter">
                    Clear
                </a>
            @endif

        </form>


        {{-- TEMPLATE LIST --}}
        <div class="workflow-template-list">

            @forelse($templates as $template)

                <div class="workflow-template-card"
                     id="template-{{ $template->id }}">

                    {{-- TEMPLATE HEADER --}}
                    <div class="workflow-template-top">

                        <div class="workflow-template-main">

                            <div class="workflow-template-info">

                                <div class="workflow-template-title-row">
                                    <h3>{{ $template->name }}</h3>

                                    <span class="workflow-status {{ $template->is_active ? 'is-active' : 'is-inactive' }}">
                                        {{ $template->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </div>

                                <p>
                                    {{ $template->description ?: 'No description provided.' }}
                                </p>

                                <div class="workflow-template-meta">
                                    <span>
                                        <strong>Process:</strong>
                                        {{ ucfirst($template->process_type) }}
                                    </span>

                                    <span>
                                        <strong>Version:</strong>
                                        {{ $template->version }}
                                    </span>

                                    <span>
                                        <strong>Stages:</strong>
                                        {{ $template->steps_count }}
                                    </span>

                                    <span>
                                        <strong>Created by:</strong>
                                        {{ $template->creator?->name ?? 'System' }}
                                    </span>
                                </div>

                            </div>
                        </div>


                        {{-- TEMPLATE ACTIONS --}}
                        <div class="workflow-template-actions">

                            {{-- VIEW STEPS --}}
                            <button type="button"
                                    class="workflow-action-btn"
                                    onclick="openWorkflowStepsModal({{ $template->id }})">
                                View Steps
                            </button>

                            @can('workflow-templates.update')
                                <button type="button"
                                        class="workflow-action-btn"
                                        title="Edit template"
                                        onclick="editWorkflowModal(
                                            {{ $template->id }},
                                            @js($template->name),
                                            @js($template->process_type),
                                            @js($template->description)
                                        )">
                                    Edit
                                </button>
                            @endcan

                            @can('workflow-templates.activate')
                                <form method="POST"
                                      action="{{ route('workflow-templates.toggle-status', $template) }}"
                                      onsubmit="return confirm('Are you sure you want to {{ $template->is_active ? 'deactivate' : 'activate' }} this workflow template?')">
                                    @csrf

                                    <button type="submit"
                                            class="workflow-action-btn {{ $template->is_active ? 'action-warning' : 'action-success' }}">
                                        {{ $template->is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>
                            @endcan

                            @can('workflow-templates.delete')
                                <form method="POST"
                                      action="{{ route('workflow-templates.destroy', $template) }}"
                                      onsubmit="return confirm('Delete this workflow template? This action cannot be undone.')">
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit"
                                            class="workflow-action-btn action-danger"
                                            {{ $template->is_active ? 'disabled' : '' }}>
                                        Delete
                                    </button>
                                </form>
                            @endcan

                        </div>
                    </div>


                    {{-- VIEW STEPS MODAL --}}
                    <div class="workflow-modal-overlay workflow-steps-modal"
                         id="workflow-details-{{ $template->id }}"
                         hidden
                         onclick="if(event.target === this) closeWorkflowStepsModal({{ $template->id }})">

                        <div class="workflow-modal workflow-steps-modal-content">

                            {{-- MODAL HEADER --}}
                            <div class="workflow-modal-header">
                                <div>
                                    <h3>{{ $template->name }}</h3>
                                    <p>Workflow stages and reviewer assignments</p>
                                </div>

                                <button type="button"
                                        class="workflow-modal-close"
                                        onclick="closeWorkflowStepsModal({{ $template->id }})">
                                    ×
                                </button>
                            </div>


                            {{-- MODAL BODY --}}
                            <div class="workflow-steps-modal-body">

                                <div class="workflow-details-header">
                                    <div>
                                        <h4>Workflow Stages</h4>
                                        <p>
                                            Configure the sequence and reviewer assignments
                                            for this workflow.
                                        </p>
                                    </div>

                                    @if($template->is_active)
                                        <span class="workflow-locked-label">
                                            <span>🔒</span>
                                            Deactivate to make changes
                                        </span>
                                    @endif
                                </div>


                                {{-- STAGE LIST --}}
                                <div class="workflow-stage-list"
                                     id="stage-list-{{ $template->id }}">

                                    @forelse($template->steps as $step)

                                        <div class="workflow-stage-card"
                                             data-step-id="{{ $step->id }}">

                                            {{-- STAGE HEADING --}}
                                            <div class="workflow-stage-heading">

                                                <div class="workflow-stage-number">
                                                    {{ $step->step_order }}
                                                </div>

                                                <div class="workflow-stage-info">
                                                    <h5>{{ $step->step_name }}</h5>

                                                    <div class="workflow-stage-tags">
                                                        <span class="workflow-tag">
                                                            {{ ucfirst($step->action_type) }}
                                                        </span>

                                                        <span class="workflow-tag">
                                                            {{ $step->completion_rule === 'all' ? 'All reviewers' : 'Any reviewer' }}
                                                        </span>

                                                        @if($step->due_days)
                                                            <span class="workflow-tag">
                                                                {{ $step->due_days }} day(s)
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>


                                                {{-- STAGE ACTIONS --}}
                                                <div class="workflow-stage-actions">

                                                    @can('workflow-templates.update')

                                                        <button type="button"
                                                                class="workflow-small-btn"
                                                                title="Move stage up"
                                                                {{ $template->is_active || $loop->first ? 'disabled' : '' }}
                                                                onclick="moveWorkflowStep({{ $template->id }}, {{ $step->id }}, 'up')">
                                                            ↑
                                                        </button>

                                                        <button type="button"
                                                                class="workflow-small-btn"
                                                                title="Move stage down"
                                                                {{ $template->is_active || $loop->last ? 'disabled' : '' }}
                                                                onclick="moveWorkflowStep({{ $template->id }}, {{ $step->id }}, 'down')">
                                                            ↓
                                                        </button>

                                                        <button type="button"
                                                                class="workflow-small-btn"
                                                                {{ $template->is_active ? 'disabled' : '' }}
                                                                onclick="editWorkflowStep(
                                                                    {{ $template->id }},
                                                                    {{ $step->id }},
                                                                    @js($step->step_name),
                                                                    @js($step->action_type),
                                                                    @js($step->completion_rule),
                                                                    @js($step->due_days),
                                                                    {{ $step->allow_return ? 'true' : 'false' }},
                                                                    {{ $step->allow_reject ? 'true' : 'false' }},
                                                                    {{ $step->allow_not_applicable ? 'true' : 'false' }}
                                                                )">
                                                            Edit
                                                        </button>

                                                        <form method="POST"
                                                              action="{{ route('workflow-templates.steps.destroy', [$template, $step]) }}"
                                                              onsubmit="return confirm('Delete this workflow stage?')">
                                                            @csrf
                                                            @method('DELETE')

                                                            <button type="submit"
                                                                    class="workflow-small-btn action-danger"
                                                                    {{ $template->is_active ? 'disabled' : '' }}>
                                                                ×
                                                            </button>
                                                        </form>

                                                    @endcan

                                                </div>
                                            </div>


                                            {{-- STAGE SETTINGS --}}
                                            <div class="workflow-stage-settings">

                                                <span>
                                                    <strong>Return:</strong>
                                                    {{ $step->allow_return ? 'Allowed' : 'Not allowed' }}
                                                </span>

                                                <span>
                                                    <strong>Reject:</strong>
                                                    {{ $step->allow_reject ? 'Allowed' : 'Not allowed' }}
                                                </span>

                                                <span>
                                                    <strong>Not Applicable:</strong>
                                                    {{ $step->allow_not_applicable ? 'Allowed' : 'Not allowed' }}
                                                </span>

                                            </div>


                                            {{-- REVIEWER ASSIGNMENTS --}}
                                            <div class="workflow-assignee-section">

                                                <div class="workflow-assignee-heading">
                                                    <h6>Reviewer Assignments</h6>
                                                    <span>{{ $step->assignees->count() }} assigned</span>
                                                </div>


                                                @if($step->assignees->isNotEmpty())

                                                    <div class="workflow-assignee-list">

                                                        @foreach($step->assignees as $assignee)

                                                            @php
                                                                $assignmentLabel = match($assignee->assignment_type) {
                                                                    'office' => $offices->firstWhere('id', $assignee->assignment_id)?->name,
                                                                    'user' => $users->firstWhere('id', $assignee->assignment_id)?->name,
                                                                    'role' => $roles->firstWhere('id', $assignee->assignment_id)?->name,
                                                                    'position' => $positions->firstWhere('id', $assignee->assignment_id)?->title,
                                                                    'division' => $divisions->firstWhere('id', $assignee->assignment_id)?->name,
                                                                    'organizational_unit' => $organizationalUnits->firstWhere('id', $assignee->assignment_id)?->name,
                                                                    default => null,
                                                                };
                                                            @endphp

                                                            <div class="workflow-assignee-item">

                                                                <div class="workflow-assignee-avatar">
                                                                    {{ strtoupper(substr($assignee->assignment_type, 0, 1)) }}
                                                                </div>

                                                                <div>
                                                                    <strong>
                                                                        {{ $assignmentLabel ?: 'Assignment #' . $assignee->assignment_id }}
                                                                    </strong>

                                                                    <small>
                                                                        {{ ucwords(str_replace('_', ' ', $assignee->assignment_type)) }}
                                                                    </small>
                                                                </div>

                                                                @can('workflow-templates.update')
                                                                    <form method="POST"
                                                                          action="{{ route('workflow-templates.assignees.destroy', [$template, $step, $assignee]) }}"
                                                                          onsubmit="return confirm('Remove this reviewer assignment?')">
                                                                        @csrf
                                                                        @method('DELETE')

                                                                        <button type="submit"
                                                                                class="workflow-remove-assignee"
                                                                                {{ $template->is_active ? 'disabled' : '' }}>
                                                                            ×
                                                                        </button>
                                                                    </form>
                                                                @endcan

                                                            </div>

                                                        @endforeach

                                                    </div>

                                                @else

                                                    <div class="workflow-no-assignee">
                                                        No reviewers assigned to this stage.
                                                    </div>

                                                @endif


                                                {{-- ADD REVIEWER --}}
                                                @can('workflow-templates.update')
                                                    @if(!$template->is_active)

                                                        <form method="POST"
                                                              action="{{ route('workflow-templates.assignees.store', [$template, $step]) }}"
                                                              class="workflow-assignee-form">
                                                            @csrf

                                                            <div class="workflow-form-field">
                                                                <label>Reviewer Type</label>

                                                                <select class="workflow-assignment-type"
                                                                        required>
                                                                    <option value="">Select type</option>
                                                                    <option value="user">Specific User</option>
                                                                    <option value="role">Role</option>
                                                                    <option value="position">Position</option>
                                                                    <option value="division">Division</option>
                                                                    <option value="office">Office</option>
                                                                    <option value="organizational_unit">Organizational Unit</option>
                                                                </select>
                                                            </div>

                                                            <div class="workflow-form-field workflow-assignment-target">
                                                                <label>Reviewer</label>

                                                                <select class="workflow-assignment-choice"
                                                                        required
                                                                        disabled>
                                                                    <option value="">Select reviewer type first</option>
                                                                </select>
                                                            </div>

                                                            <input type="hidden"
                                                                   name="assignment_type"
                                                                   class="workflow-assignment-type-value">

                                                            <input type="hidden"
                                                                   name="assignment_id"
                                                                   class="workflow-assignment-id-value">

                                                            <button type="submit"
                                                                    class="workflow-btn workflow-btn-secondary workflow-add-assignee">
                                                                + Add Reviewer
                                                            </button>

                                                        </form>

                                                    @endif
                                                @endcan

                                            </div>

                                        </div>

                                    @empty

                                        <div class="workflow-empty-stages">
                                            <div class="workflow-empty-icon">☷</div>
                                            <strong>No workflow stages yet</strong>
                                            <p>
                                                Add the first stage to define how this
                                                workflow should proceed.
                                            </p>
                                        </div>

                                    @endforelse

                                </div>


                                {{-- ADD STAGE --}}
                                @can('workflow-templates.create')
                                    @if(!$template->is_active)

                                        <div class="workflow-add-stage-panel">

                                            <h5>+ Add Workflow Stage</h5>

                                            <form method="POST"
                                                  action="{{ route('workflow-templates.steps.store', $template) }}"
                                                  class="workflow-stage-form">
                                                @csrf

                                                <div class="workflow-form-grid">

                                                    <div class="workflow-form-field workflow-field-wide">
                                                        <label>Stage Name <span>*</span></label>

                                                        <input type="text"
                                                               name="step_name"
                                                               placeholder="e.g. Division Review"
                                                               maxlength="150"
                                                               required>
                                                    </div>

                                                    <div class="workflow-form-field">
                                                        <label>Action Type <span>*</span></label>

                                                        <select name="action_type" required>
                                                            <option value="review">Review</option>
                                                            <option value="approval">Approval</option>
                                                        </select>
                                                    </div>

                                                    <div class="workflow-form-field">
                                                        <label>Completion Rule <span>*</span></label>

                                                        <select name="completion_rule" required>
                                                            <option value="all">All reviewers must act</option>
                                                            <option value="any">Any reviewer can complete</option>
                                                        </select>
                                                    </div>

                                                    <div class="workflow-form-field">
                                                        <label>Due Days</label>

                                                        <input type="number"
                                                               name="due_days"
                                                               min="1"
                                                               max="365"
                                                               placeholder="Optional">
                                                    </div>

                                                </div>


                                                <div class="workflow-checkbox-group">

                                                    <label>
                                                        <input type="checkbox"
                                                               name="allow_return"
                                                               value="1"
                                                               checked>
                                                        Allow Return
                                                    </label>

                                                    <label>
                                                        <input type="checkbox"
                                                               name="allow_reject"
                                                               value="1">
                                                        Allow Reject
                                                    </label>

                                                    <label>
                                                        <input type="checkbox"
                                                               name="allow_not_applicable"
                                                               value="1">
                                                        Allow Not Applicable
                                                    </label>

                                                </div>


                                                <div class="workflow-form-footer">
                                                    <button type="submit"
                                                            class="workflow-btn workflow-btn-primary">
                                                        Add Stage
                                                    </button>
                                                </div>

                                            </form>

                                        </div>

                                    @endif
                                @endcan

                            </div>
                            {{-- END MODAL BODY --}}

                        </div>

                    </div>
                    {{-- END VIEW STEPS MODAL --}}

                </div>

            @empty

                <div class="workflow-empty-state">
                    <div class="workflow-empty-icon">▤</div>
                    <h3>No workflow templates found</h3>
                    <p>
                        Create your first workflow template to configure your organization's
                        review and approval processes.
                    </p>

                    @can('workflow-templates.create')
                        <button type="button"
                                class="workflow-btn workflow-btn-primary"
                                onclick="openWorkflowModal()">
                            + Create Workflow
                        </button>
                    @endcan
                </div>

            @endforelse

        </div>


        {{-- PAGINATION --}}
        @if($templates->hasPages())
            <div class="workflow-pagination">
                {{ $templates->links() }}
            </div>
        @endif

    </div>

</div>


{{-- CREATE / EDIT TEMPLATE MODAL --}}
<div class="workflow-modal-overlay"
     id="workflowModal"
     onclick="if(event.target === this) closeWorkflowModal()">

    <div class="workflow-modal">

        <div class="workflow-modal-header">
            <div>
                <h3 id="workflowModalTitle">Create Workflow Template</h3>
                <p>Provide the basic information for this workflow.</p>
            </div>

            <button type="button"
                    class="workflow-modal-close"
                    onclick="closeWorkflowModal()">×</button>
        </div>


        <form method="POST"
              id="workflowForm"
              action="{{ route('workflow-templates.store') }}">

            @csrf

            <input type="hidden" name="_method" id="workflowMethod" value="POST">

            <div class="workflow-modal-body">

                <div class="workflow-form-field">
                    <label>Workflow Name <span>*</span></label>

                    <input type="text"
                           name="name"
                           id="workflowName"
                           placeholder="e.g. PAP Approval Workflow"
                           maxlength="150"
                           required>
                </div>

                <div class="workflow-form-field">
                    <label>Process Type <span>*</span></label>

                    <select name="process_type"
                            id="workflowProcessType"
                            required>
                        <option value="">Select process type</option>
                        <option value="pap">PAP</option>
                        <option value="activity">Activity</option>
                        <option value="task">Task</option>
                        <option value="budget">Budget</option>
                    </select>
                </div>

                <div class="workflow-form-field">
                    <label>Description</label>

                    <textarea name="description"
                              id="workflowDescription"
                              rows="4"
                              placeholder="Describe the purpose of this workflow..."></textarea>
                </div>

                <div class="workflow-modal-note">
                    <span>ⓘ</span>
                    New workflow templates are created as inactive.
                    Add at least one stage and reviewer assignment before activation.
                </div>

            </div>

            <div class="workflow-modal-footer">
                <button type="button"
                        class="workflow-btn workflow-btn-secondary"
                        onclick="closeWorkflowModal()">
                    Cancel
                </button>

                <button type="submit"
                        class="workflow-btn workflow-btn-primary">
                    Save Workflow
                </button>
            </div>

        </form>

    </div>
</div>


{{-- EDIT STAGE MODAL --}}
<div class="workflow-modal-overlay"
     id="workflowStepModal"
     onclick="if(event.target === this) closeWorkflowStepModal()">

    <div class="workflow-modal">

        <div class="workflow-modal-header">
            <div>
                <h3>Edit Workflow Stage</h3>
                <p>Update the stage configuration.</p>
            </div>

            <button type="button"
                    class="workflow-modal-close"
                    onclick="closeWorkflowStepModal()">×</button>
        </div>

        <form method="POST" id="workflowStepForm">

            @csrf
            @method('PUT')

            <div class="workflow-modal-body">

                <div class="workflow-form-field">
                    <label>Stage Name <span>*</span></label>

                    <input type="text"
                           name="step_name"
                           id="editStepName"
                           required
                           maxlength="150">
                </div>

                <div class="workflow-form-grid">

                    <div class="workflow-form-field">
                        <label>Action Type</label>

                        <select name="action_type" id="editStepActionType">
                            <option value="review">Review</option>
                            <option value="approval">Approval</option>
                        </select>
                    </div>

                    <div class="workflow-form-field">
                        <label>Completion Rule</label>

                        <select name="completion_rule" id="editStepCompletionRule">
                            <option value="all">All reviewers</option>
                            <option value="any">Any reviewer</option>
                        </select>
                    </div>

                    <div class="workflow-form-field">
                        <label>Due Days</label>

                        <input type="number"
                               name="due_days"
                               id="editStepDueDays"
                               min="1"
                               max="365">
                    </div>

                </div>

                <div class="workflow-checkbox-group vertical">

                    <label>
                        <input type="checkbox"
                               name="allow_return"
                               value="1"
                               id="editStepAllowReturn">
                        Allow Return
                    </label>

                    <label>
                        <input type="checkbox"
                               name="allow_reject"
                               value="1"
                               id="editStepAllowReject">
                        Allow Reject
                    </label>

                    <label>
                        <input type="checkbox"
                               name="allow_not_applicable"
                               value="1"
                               id="editStepAllowNA">
                        Allow Not Applicable
                    </label>

                </div>

            </div>

            <div class="workflow-modal-footer">
                <button type="button"
                        class="workflow-btn workflow-btn-secondary"
                        onclick="closeWorkflowStepModal()">
                    Cancel
                </button>

                <button type="submit"
                        class="workflow-btn workflow-btn-primary">
                    Save Changes
                </button>
            </div>

        </form>

    </div>
</div>


{{-- HIDDEN FORM FOR REORDERING STAGES --}}
<form method="POST"
      id="workflowReorderForm"
      style="display:none;">
    @csrf
    @method('PUT')
    <div id="workflowReorderInputs"></div>
</form>

@endsection


@push('scripts')
<script>
/*
|--------------------------------------------------------------------------
| URLs
|--------------------------------------------------------------------------
*/

const workflowStoreUrl = @json(route('workflow-templates.store'));
const workflowUpdateUrl = @json(url('/workflow-templates'));


/*
|--------------------------------------------------------------------------
| Modal Utilities
|--------------------------------------------------------------------------
*/

function lockWorkflowBody() {
    document.body.classList.add('workflow-modal-open');
}

function unlockWorkflowBody() {
    if (!document.querySelector('.workflow-modal-overlay.show')) {
        document.body.classList.remove('workflow-modal-open');
    }
}


/*
|--------------------------------------------------------------------------
| Create / Edit Workflow Template
|--------------------------------------------------------------------------
*/

function openWorkflowModal() {
    const form = document.getElementById('workflowForm');

    form.reset();

    form.action = workflowStoreUrl;

    document.getElementById('workflowMethod').value = 'POST';

    document.getElementById('workflowModalTitle').textContent =
        'Create Workflow Template';

    document.getElementById('workflowModal').classList.add('show');

    lockWorkflowBody();
}

function editWorkflowModal(id, name, processType, description) {
    const form = document.getElementById('workflowForm');

    form.action = `${workflowUpdateUrl}/${id}`;

    document.getElementById('workflowMethod').value = 'PUT';
    document.getElementById('workflowName').value = name;
    document.getElementById('workflowProcessType').value = processType;
    document.getElementById('workflowDescription').value = description || '';

    document.getElementById('workflowModalTitle').textContent =
        'Edit Workflow Template';

    document.getElementById('workflowModal').classList.add('show');

    lockWorkflowBody();
}

function closeWorkflowModal() {
    document.getElementById('workflowModal').classList.remove('show');
    unlockWorkflowBody();
}


/*
|--------------------------------------------------------------------------
| View Workflow Steps Modal
|--------------------------------------------------------------------------
*/

function openWorkflowStepsModal(id) {
    const modal = document.getElementById(`workflow-details-${id}`);

    if (!modal) return;

    modal.hidden = false;
    modal.classList.add('show');

    lockWorkflowBody();
}

function closeWorkflowStepsModal(id) {
    const modal = document.getElementById(`workflow-details-${id}`);

    if (!modal) return;

    modal.classList.remove('show');
    modal.hidden = true;

    unlockWorkflowBody();
}


/*
|--------------------------------------------------------------------------
| Edit Workflow Stage
|--------------------------------------------------------------------------
*/

function editWorkflowStep(
    templateId,
    stepId,
    stepName,
    actionType,
    completionRule,
    dueDays,
    allowReturn,
    allowReject,
    allowNA
) {
    const form = document.getElementById('workflowStepForm');

    form.action = `${workflowUpdateUrl}/${templateId}/steps/${stepId}`;

    document.getElementById('editStepName').value = stepName;
    document.getElementById('editStepActionType').value = actionType;
    document.getElementById('editStepCompletionRule').value = completionRule;
    document.getElementById('editStepDueDays').value = dueDays || '';

    document.getElementById('editStepAllowReturn').checked = allowReturn;
    document.getElementById('editStepAllowReject').checked = allowReject;
    document.getElementById('editStepAllowNA').checked = allowNA;

    document.getElementById('workflowStepModal').classList.add('show');

    lockWorkflowBody();
}

function closeWorkflowStepModal() {
    document.getElementById('workflowStepModal').classList.remove('show');
    unlockWorkflowBody();
}


/*
|--------------------------------------------------------------------------
| Reviewer Assignment Options
|--------------------------------------------------------------------------
*/

const workflowAssignmentOptions = {
    office: @json($offices->map(fn($item) => [
        'id' => $item->id,
        'name' => $item->name
    ])->values()),

    user: @json($users->map(fn($item) => [
        'id' => $item->id,
        'name' => $item->name
    ])->values()),

    role: @json($roles->map(fn($item) => [
        'id' => $item->id,
        'name' => $item->name
    ])->values()),

    position: @json($positions->map(fn($item) => [
        'id' => $item->id,
        'name' => $item->title
    ])->values()),

    division: @json($divisions->map(fn($item) => [
        'id' => $item->id,
        'name' => $item->name
    ])->values()),

    organizational_unit: @json($organizationalUnits->map(fn($item) => [
        'id' => $item->id,
        'name' => $item->name
    ])->values())
};


/*
|--------------------------------------------------------------------------
| Reviewer Assignment Selector
|--------------------------------------------------------------------------
*/

document.querySelectorAll('.workflow-assignee-form').forEach(form => {

    const typeSelect = form.querySelector('.workflow-assignment-type');
    const choiceSelect = form.querySelector('.workflow-assignment-choice');

    const typeValue = form.querySelector('.workflow-assignment-type-value');
    const idValue = form.querySelector('.workflow-assignment-id-value');

    typeSelect.addEventListener('change', function () {

        const type = this.value;
        const options = workflowAssignmentOptions[type] || [];

        choiceSelect.innerHTML = '';

        const defaultOption = document.createElement('option');
        defaultOption.value = '';
        defaultOption.textContent = 'Select reviewer';

        choiceSelect.appendChild(defaultOption);

        options.forEach(item => {

            const option = document.createElement('option');

            option.value = item.id;
            option.textContent = item.name;

            choiceSelect.appendChild(option);

        });

        choiceSelect.disabled = !type || options.length === 0;

        if (type && options.length === 0) {
            choiceSelect.innerHTML =
                '<option value="">No available records</option>';
        }

        typeValue.value = '';
        idValue.value = '';

    });


    choiceSelect.addEventListener('change', function () {

        typeValue.value = typeSelect.value;
        idValue.value = this.value;

    });


    form.addEventListener('submit', function (event) {

        if (!typeSelect.value || !choiceSelect.value) {
            event.preventDefault();

            alert('Please select a reviewer type and reviewer.');

            return;
        }

        typeValue.value = typeSelect.value;
        idValue.value = choiceSelect.value;

    });

});


/*
|--------------------------------------------------------------------------
| Reorder Workflow Stages
|--------------------------------------------------------------------------
*/

function moveWorkflowStep(templateId, stepId, direction) {

    const list = document.getElementById(`stage-list-${templateId}`);

    if (!list) return;

    const cards = Array.from(
        list.querySelectorAll('.workflow-stage-card')
    );

    const currentIndex = cards.findIndex(card =>
        Number(card.dataset.stepId) === Number(stepId)
    );

    if (currentIndex === -1) return;

    const targetIndex = direction === 'up'
        ? currentIndex - 1
        : currentIndex + 1;

    if (targetIndex < 0 || targetIndex >= cards.length) return;

    const targetCard = cards[targetIndex];
    const currentCard = cards[currentIndex];

    if (direction === 'up') {
        list.insertBefore(currentCard, targetCard);
    } else {
        list.insertBefore(targetCard, currentCard);
    }

    const orderedIds = Array.from(
        list.querySelectorAll('.workflow-stage-card')
    ).map(card => card.dataset.stepId);

    const form = document.getElementById('workflowReorderForm');
    const inputs = document.getElementById('workflowReorderInputs');

    form.action = `${workflowUpdateUrl}/${templateId}/reorder-steps`;

    inputs.innerHTML = '';

    orderedIds.forEach(id => {

        const input = document.createElement('input');

        input.type = 'hidden';
        input.name = 'steps[]';
        input.value = id;

        inputs.appendChild(input);

    });

    form.submit();
}


/*
|--------------------------------------------------------------------------
| Escape Key Handling
|--------------------------------------------------------------------------
*/

document.addEventListener('keydown', function(event) {

    if (event.key !== 'Escape') return;

    // Close the topmost modal first.
    const stepModal = document.getElementById('workflowStepModal');

    if (stepModal.classList.contains('show')) {
        closeWorkflowStepModal();
        return;
    }

    const templateModal = document.getElementById('workflowModal');

    if (templateModal.classList.contains('show')) {
        closeWorkflowModal();
        return;
    }

    const openStepsModal = document.querySelector('.workflow-steps-modal.show');

    if (openStepsModal) {
        const id = openStepsModal.id.replace('workflow-details-', '');
        closeWorkflowStepsModal(id);
    }

});


/*
|--------------------------------------------------------------------------
| Reopen Modal After Validation Errors
|--------------------------------------------------------------------------
*/

@if($errors->any())

    @if(old('_method') === 'PUT' && old('action_type'))

        document.getElementById('workflowStepModal').classList.add('show');
        lockWorkflowBody();

    @else

        document.getElementById('workflowModal').classList.add('show');
        lockWorkflowBody();

    @endif

@endif

</script>
@endpush