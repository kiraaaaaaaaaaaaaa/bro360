
@extends('layouts.app')

@section('title', 'Add Task Type')
@section('page-title', 'Add Task Type')
@section('page-description', 'Create a standardized task title and assign its default workflow.')

@section('content')

<div class="content-section">

    <div class="section-header">
        <div>
            <h3>New Task Type</h3>
            <p>Define the task information and its associated workflow.</p>
        </div>

        <a href="{{ route('task-types.index') }}" class="btn btn-secondary">
            Back to Task Types
        </a>
    </div>

    <form action="{{ route('task-types.store') }}" method="POST">

        @csrf

        <div class="form-grid">

            <div class="form-group">
                <label for="title">Task Title <span class="text-danger">*</span></label>

                <input
                    type="text"
                    id="title"
                    name="title"
                    value="{{ old('title') }}"
                    class="form-control"
                    placeholder="e.g. Activity Proposal Form (APF)"
                    required
                >

                @error('title')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <div class="form-group">
                <label for="category">Category</label>

                <input
                    type="text"
                    id="category"
                    name="category"
                    value="{{ old('category') }}"
                    class="form-control"
                    placeholder="e.g. Planning, Administrative"
                >

                @error('category')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <div class="form-group full-width">
                <label for="description">Description</label>

                <textarea
                    id="description"
                    name="description"
                    class="form-control"
                    rows="3"
                    placeholder="Enter a brief description of this task type."
                >{{ old('description') }}</textarea>

                @error('description')
                    <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <div class="form-group full-width">
                <label for="workflow_template_id">
                    Default Workflow <span class="text-danger">*</span>
                </label>

                <select
                    id="workflow_template_id"
                    name="workflow_template_id"
                    class="form-control"
                    required
                >
                    <option value="">Select Workflow Template</option>

                    @foreach($workflowTemplates as $workflow)
                        <option
                            value="{{ $workflow->id }}"
                            {{ old('workflow_template_id') == $workflow->id ? 'selected' : '' }}
                        >
                            {{ $workflow->name }}
                        </option>
                    @endforeach

                </select>

                @error('workflow_template_id')
                    <small class="text-danger">{{ $message }}</small>
                @enderror

                <small class="form-text">
                    This workflow will automatically be assigned whenever
                    this task type is selected during task creation.
                </small>
            </div>

        </div>

        <div class="form-actions">
            <a href="{{ route('task-types.index') }}" class="btn btn-secondary">
                Cancel
            </a>

            <button type="submit" class="btn btn-primary">
                Save Task Type
            </button>
        </div>

    </form>

</div>

@endsection
