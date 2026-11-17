@extends('layouts.app')

@section('title', 'Program Management')

@section('content')
<div class="program-page">

    {{-- PAGE HEADER --}}
    <div class="program-header">
        <div>
            <div class="program-breadcrumb">
                Budget Management <i class="fa-solid fa-chevron-right"></i> Program Management
            </div>
            <h1>Program Management</h1>
            <p>Manage and maintain the programs implemented by the Bangsamoro Revenue Office.</p>
        </div>

        @can('programs.create')
            <button type="button"
                    class="program-btn program-btn-primary"
                    onclick="openProgramModal()">
                <i class="fa-solid fa-plus"></i>
                Add Program
            </button>
        @endcan
    </div>

    {{-- FLASH MESSAGES --}}
    @if(session('success'))
        <div class="program-alert program-alert-success">
            <i class="fa-solid fa-circle-check"></i>
            <span>{{ session('success') }}</span>
            <button type="button" onclick="this.parentElement.remove()">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="program-alert program-alert-error">
            <i class="fa-solid fa-circle-exclamation"></i>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="program-alert program-alert-error">
            <div>
                <strong>Please check the following:</strong>
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    {{-- SUMMARY CARDS --}}
    <div class="program-summary-grid">

        <div class="program-summary-card">
            <div class="program-summary-icon program-icon-total">
                <i class="fa-solid fa-diagram-project"></i>
            </div>
            <div>
                <span>Total Programs</span>
                <h2>{{ number_format($counts['total']) }}</h2>
                <small>All registered programs</small>
            </div>
        </div>

        <div class="program-summary-card">
            <div class="program-summary-icon program-icon-active">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div>
                <span>Active Programs</span>
                <h2>{{ number_format($counts['active']) }}</h2>
                <small>Available for selection</small>
            </div>
        </div>

        <div class="program-summary-card">
            <div class="program-summary-icon program-icon-inactive">
                <i class="fa-solid fa-circle-pause"></i>
            </div>
            <div>
                <span>Inactive Programs</span>
                <h2>{{ number_format($counts['inactive']) }}</h2>
                <small>Currently unavailable</small>
            </div>
        </div>

    </div>

    {{-- MAIN CONTENT --}}
    <div class="program-panel">

        <div class="program-panel-header">
            <div>
                <h3>Program Registry</h3>
                <p>View, search, and manage registered programs.</p>
            </div>

            <span class="program-record-count">
                {{ $programs->total() }} records
            </span>
        </div>

        {{-- FILTERS --}}
        <form method="GET"
              action="{{ route('programs.index') }}"
              class="program-filter-form">

            <div class="program-search-box">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Search by program name, code, or description...">
            </div>

            <select name="status" class="program-filter-select">
                <option value="">All Status</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>
                    Active
                </option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>
                    Inactive
                </option>
            </select>

            <button type="submit" class="program-btn program-btn-filter">
                <i class="fa-solid fa-filter"></i>
                Filter
            </button>

            @if(request()->hasAny(['search', 'status']))
                <a href="{{ route('programs.index') }}"
                   class="program-btn program-btn-reset">
                    Reset
                </a>
            @endif

        </form>

        {{-- TABLE --}}
        <div class="program-table-wrapper">
            <table class="program-table">
                <thead>
                    <tr>
                        <th style="width: 65px;">#</th>
                        <th>Program</th>
                        <th>Program Code</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th class="program-actions-heading">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($programs as $index => $program)
                        <tr>
                        
                            <td class="program-row-number">
                                {{ $programs->firstItem() + $index }}
                            </td>

                            <td>
                                <div class="program-name-cell">
                                    <div class="program-name-icon">
                                        <i class="fa-solid fa-layer-group"></i>
                                    </div>
                                    <div>
                                        <strong>{{ $program->name }}</strong>
                                        <small>Registered Program</small>
                                    </div>
                                </div>
                            </td>

                            <td>
                                @if($program->code)
                                    <span class="program-code">
                                        {{ $program->code }}
                                    </span>
                                @else
                                    <span class="program-empty">Not specified</span>
                                @endif
                            </td>

                            <td>
                                <span class="program-description"
                                      title="{{ $program->description }}">
                                    {{ $program->description ?: 'No description provided' }}
                                </span>
                            </td>

                            <td>
                                @if($program->is_active)
                                    <span class="program-status program-status-active">
                                        <span></span> Active
                                    </span>
                                @else
                                    <span class="program-status program-status-inactive">
                                        <span></span> Inactive
                                    </span>
                                @endif
                            </td>

                            <td>
                                <div class="program-action-buttons">

                                    @can('programs.update')
                                        <button type="button"
                                                class="program-action-btn program-edit-btn"
                                                onclick="editProgram(
                                                    {{ $program->id }},
                                                    @js($program->code),
                                                    @js($program->name),
                                                    @js($program->description)
                                                )">
                                            Edit
                                        </button>

                                        <form method="POST"
                                              action="{{ route('programs.toggle-status', $program) }}"
                                              onsubmit="return confirm('Are you sure you want to {{ $program->is_active ? 'deactivate' : 'activate' }} this program?');">
                                            @csrf

                                            <button type="submit"
                                                    class="program-action-btn {{ $program->is_active ? 'program-deactivate-btn' : 'program-activate-btn' }}">
                                                {{ $program->is_active ? 'Deactivate' : 'Activate' }}
                                            </button>
                                        </form>
                                    @endcan

                                    @if(!auth()->user()->can('programs.update'))
                                        <span class="program-empty">No actions</span>
                                    @endif

                                     <a href="{{ route('programs.outputs.index', $program) }}" class="program-output-action edit">
                                    <i class="fa-solid fa-diagram-project"></i>
                                    Outputs
                                </a>

                                </div>
                            </td>
                        </tr>

                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="program-empty-state">
                                    <div class="program-empty-icon">
                                        <i class="fa-solid fa-diagram-project"></i>
                                    </div>

                                    <h3>No programs found</h3>

                                    <p>
                                        @if(request()->hasAny(['search', 'status']))
                                            Try changing your search or filters.
                                        @else
                                            Start by registering your first program.
                                        @endif
                                    </p>

                                    @can('programs.create')
                                        <button type="button"
                                                class="program-btn program-btn-primary"
                                                onclick="openProgramModal()">
                                            <i class="fa-solid fa-plus"></i>
                                            Add Program
                                        </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- PAGINATION --}}
        @if($programs->hasPages())
            <div class="program-pagination">
                <div class="program-pagination-info">
                    Showing
                    <strong>{{ $programs->firstItem() }}</strong>
                    to
                    <strong>{{ $programs->lastItem() }}</strong>
                    of
                    <strong>{{ $programs->total() }}</strong>
                    records
                </div>

                {{ $programs->links() }}
            </div>
        @endif

    </div>
</div>

{{-- ADD / EDIT MODAL --}}
<div class="program-modal-overlay" id="programModal" hidden>
    <div class="program-modal">

        <div class="program-modal-header">
            <div>
                <span class="program-modal-eyebrow">BUDGET MANAGEMENT</span>
                <h2 id="programModalTitle">Add Program</h2>
                <p id="programModalSubtitle">Register a new BRO program.</p>
            </div>

            <button type="button"
                    class="program-modal-close"
                    onclick="closeProgramModal()">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form id="programForm"
              method="POST"
              action="{{ route('programs.store') }}">

            @csrf

            <div id="programMethod"></div>

            {{-- Used to recover the record ID after validation redirects --}}
            <input type="hidden"
                   name="program_id"
                   id="program_id"
                   value="{{ old('program_id') }}">

            <div class="program-modal-body">

                <div class="program-form-field">
                    <label for="program_code">
                        Program Code
                        <span class="program-optional">Optional</span>
                    </label>

                    <input type="text"
                           id="program_code"
                           name="code"
                           maxlength="60"
                           value="{{ old('code') }}"
                           placeholder="e.g. BRO-PRG-001">

                    <small>A unique identifier for this program.</small>
                </div>

                <div class="program-form-field">
                    <label for="program_name">
                        Program Name <span class="program-required">*</span>
                    </label>

                    <input type="text"
                           id="program_name"
                           name="name"
                           maxlength="180"
                           value="{{ old('name') }}"
                           placeholder="Enter program name"
                           required>
                </div>

                <div class="program-form-field">
                    <label for="program_description">
                        Description
                        <span class="program-optional">Optional</span>
                    </label>

                    <textarea id="program_description"
                              name="description"
                              rows="4"
                              placeholder="Provide details about this program...">{{ old('description') }}</textarea>
                </div>

                <div class="program-form-note">
                    <i class="fa-solid fa-circle-info"></i>
                    <span>
                        Newly registered programs will be active by default
                        and available for selection in applicable modules.
                    </span>
                </div>

            </div>

            <div class="program-modal-footer">
                <button type="button"
                        class="program-btn program-btn-reset"
                        onclick="closeProgramModal()">
                    Cancel
                </button>

                <button type="submit"
                        class="program-btn program-btn-primary"
                        id="programSubmitBtn">
                    <i class="fa-solid fa-floppy-disk"></i>
                    Save Program
                </button>
            </div>

        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
    const programBaseUrl = @json(url('/programs'));
    const programStoreUrl = @json(route('programs.store'));

    function openProgramModal() {
        const modal = document.getElementById('programModal');
        const form = document.getElementById('programForm');

        form.reset();
        form.action = programStoreUrl;

        document.getElementById('programMethod').innerHTML = '';
        document.getElementById('program_id').value = '';

        document.getElementById('programModalTitle').textContent = 'Add Program';
        document.getElementById('programModalSubtitle').textContent =
            'Register a new BRO program.';

        document.getElementById('programSubmitBtn').innerHTML =
            '<i class="fa-solid fa-floppy-disk"></i> Save Program';

        modal.hidden = false;
        modal.classList.add('show');
        document.body.classList.add('program-modal-open');

        setTimeout(() => {
            document.getElementById('program_name').focus();
        }, 100);
    }

    function editProgram(id, code, name, description) {
        const modal = document.getElementById('programModal');
        const form = document.getElementById('programForm');

        form.reset();
        form.action = `${programBaseUrl}/${id}`;

        document.getElementById('programMethod').innerHTML =
            '<input type="hidden" name="_method" value="PUT">';

        document.getElementById('program_id').value = id;
        document.getElementById('program_code').value = code ?? '';
        document.getElementById('program_name').value = name ?? '';
        document.getElementById('program_description').value = description ?? '';

        document.getElementById('programModalTitle').textContent = 'Edit Program';
        document.getElementById('programModalSubtitle').textContent =
            'Update the details of this program.';

        document.getElementById('programSubmitBtn').innerHTML =
            '<i class="fa-solid fa-floppy-disk"></i> Update Program';

        modal.hidden = false;
        modal.classList.add('show');
        document.body.classList.add('program-modal-open');

        setTimeout(() => {
            document.getElementById('program_name').focus();
        }, 100);
    }

    function closeProgramModal() {
        const modal = document.getElementById('programModal');

        modal.classList.remove('show');
        modal.hidden = true;

        document.body.classList.remove('program-modal-open');
    }

    document.getElementById('programModal').addEventListener('click', function(event) {
        if (event.target === this) {
            closeProgramModal();
        }
    });

    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeProgramModal();
        }
    });

    // Restore form values and modal after validation failure.
    @if($errors->any())
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('programModal');
            const form = document.getElementById('programForm');

            @if(old('_method') === 'PUT' && old('program_id'))
                const programId = @json(old('program_id'));

                form.action = `${programBaseUrl}/${programId}`;

                document.getElementById('programMethod').innerHTML =
                    '<input type="hidden" name="_method" value="PUT">';

                document.getElementById('programModalTitle').textContent = 'Edit Program';
                document.getElementById('programModalSubtitle').textContent =
                    'Update the details of this program.';

                document.getElementById('programSubmitBtn').innerHTML =
                    '<i class="fa-solid fa-floppy-disk"></i> Update Program';
            @endif

            modal.hidden = false;
            modal.classList.add('show');
            document.body.classList.add('program-modal-open');
        });
    @endif
</script>
@endpush