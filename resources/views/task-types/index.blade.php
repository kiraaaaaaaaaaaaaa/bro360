
@extends('layouts.app')

@section('title', 'Task Types')
@section('page-title', 'Task Types')
@section('page-description', 'Manage standardized task titles and their associated workflows.')

@section('content')

<div class="content-section task-types-page">

    <div class="section-header">
        <div>
            <h3>Task Types</h3>
            <p>Manage standardized task titles and default workflows.</p>
        </div>

        <button type="button" class="btn btn-primary" onclick="openTaskTypeModal()">
            Add Task Type
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Task Title</th>
                    <th>Category</th>
                    <th>Default Workflow</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                @forelse($taskTypes as $taskType)
                    <tr>
                        <td>
                            <strong>{{ $taskType->title }}</strong>
                            @if($taskType->description)
                                <br>
                                <small>{{ $taskType->description }}</small>
                            @endif
                        </td>

                        <td>{{ $taskType->category ?? '—' }}</td>

                        <td>
                            {{ $taskType->workflowTemplate?->name ?? 'No workflow assigned' }}
                        </td>

                        <td>
                            @if($taskType->is_active)
                                <span class="status-badge status-completed">Active</span>
                            @else
                                <span class="status-badge status-pending">Inactive</span>
                            @endif
                        </td>

                        <td>
                            <div class="action-buttons">

                                <button
                                    type="button"
                                    class="btn btn-secondary btn-sm"
                                    onclick="editTaskType(this)"
                                    data-id="{{ $taskType->id }}"
                                    data-title="{{ $taskType->title }}"
                                    data-category="{{ $taskType->category }}"
                                    data-description="{{ $taskType->description }}"
                                    data-workflow="{{ $taskType->workflow_template_id }}"
                                    data-update-url="{{ route('task-types.update', $taskType) }}"
                                >
                                    Edit
                                </button>

                                @if($taskType->is_active)
                                    <form
                                        action="{{ route('task-types.destroy', $taskType) }}"
                                        method="POST"
                                        onsubmit="return confirm('Deactivate this task type?')"
                                    >
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit" class="btn btn-danger btn-sm">
                                            Deactivate
                                        </button>
                                    </form>
                                @endif

                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">
                            No task types have been created yet.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination-container">
        {{ $taskTypes->links() }}
    </div>

</div>


{{-- TASK TYPE MODAL --}}

<dialog id="taskTypeModal" class="task-type-modal">

    <div class="modal-header">
        <div>
            <h3 id="modalTitle">Add Task Type</h3>
            <p>Configure task information and its default workflow.</p>
        </div>

        <button type="button" class="modal-close" onclick="closeTaskTypeModal()">
            &times;
        </button>
    </div>

    <form
        id="taskTypeForm"
        action="{{ route('task-types.store') }}"
        method="POST"
    >

        @csrf

        <input type="hidden" name="_method" id="formMethod" value="">

        <div class="modal-body">

            <div class="form-group">
                <label for="task_title">Task Title <span class="text-danger">*</span></label>

                <input
                    type="text"
                    id="task_title"
                    name="title"
                    class="form-control"
                    placeholder="e.g. Activity Proposal Form (APF)"
                    required
                >
            </div>

            <div class="form-group">
                <label for="task_category">Category</label>

                <input
                    type="text"
                    id="task_category"
                    name="category"
                    class="form-control"
                    placeholder="e.g. Planning"
                >
            </div>

            <div class="form-group">
                <label for="task_description">Description</label>

                <textarea
                    id="task_description"
                    name="description"
                    class="form-control"
                    rows="3"
                    placeholder="Enter task description"
                ></textarea>
            </div>

            <div class="form-group">
                <label for="task_workflow">
                    Default Workflow <span class="text-danger">*</span>
                </label>

                <select
                    id="task_workflow"
                    name="workflow_template_id"
                    class="form-control"
                    required
                >
                    <option value="">Select Workflow Template</option>

                    @foreach($workflowTemplates as $workflow)
                        <option value="{{ $workflow->id }}">
                            {{ $workflow->name }}
                        </option>
                    @endforeach
                </select>

                <small class="form-text">
                    This workflow will automatically be assigned to newly
                    created tasks using this task type.
                </small>
            </div>

        </div>

        <div class="modal-footer">
            <button
                type="button"
                class="btn btn-secondary"
                onclick="closeTaskTypeModal()"
            >
                Cancel
            </button>

            <button type="submit" class="btn btn-primary" id="saveTaskTypeButton">
                Save Task Type
            </button>
        </div>

    </form>

</dialog>


<script>
    const taskTypeModal = document.getElementById('taskTypeModal');
    const taskTypeForm = document.getElementById('taskTypeForm');

    function openTaskTypeModal() {

        taskTypeForm.reset();

        taskTypeForm.action = "{{ route('task-types.store') }}";

        document.getElementById('formMethod').value = '';

        document.getElementById('modalTitle').textContent = 'Add Task Type';

        document.getElementById('saveTaskTypeButton').textContent = 'Save Task Type';

        taskTypeModal.showModal();
    }

    function editTaskType(button) {

        taskTypeForm.reset();

        taskTypeForm.action = button.dataset.updateUrl;

        document.getElementById('formMethod').value = 'PUT';

        document.getElementById('modalTitle').textContent = 'Edit Task Type';

        document.getElementById('saveTaskTypeButton').textContent = 'Update Task Type';

        document.getElementById('task_title').value = button.dataset.title || '';

        document.getElementById('task_category').value = button.dataset.category || '';

        document.getElementById('task_description').value = button.dataset.description || '';

        document.getElementById('task_workflow').value = button.dataset.workflow || '';

        taskTypeModal.showModal();
    }

    function closeTaskTypeModal() {
        taskTypeModal.close();
    }

    taskTypeModal.addEventListener('click', function(event) {
        if (event.target === taskTypeModal) {
            taskTypeModal.close();
        }
    });
</script>

@endsection
