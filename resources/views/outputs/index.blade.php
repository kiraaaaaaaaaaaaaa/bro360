
@extends('layouts.app')

@section('title', 'Program Outputs')

@section('content')
<div class="program-output-page">

    {{-- PAGE HEADER --}}
    <div class="program-output-header">
        <div>
            <div class="program-output-breadcrumb">
                <a href="{{ route('programs.index') }}">Program Management</a>
                <span>/</span>
                <span>Outputs</span>
            </div>

            <h1>Program Outputs</h1>
            <p>
                Manage the outputs under
                <strong>{{ $program->name }}</strong>.
            </p>
        </div>

        <div class="program-output-header-actions">
            <a href="{{ route('programs.index') }}" class="program-output-back">
                Back to Programs
            </a>

            @can('outputs.create')
                <button type="button"
                        class="program-output-primary-btn"
                        onclick="openOutputModal()">
                    + Add Output
                </button>
            @endcan
        </div>
    </div>

    {{-- PROGRAM INFORMATION --}}
    <div class="program-output-program-card">
        <div class="program-output-program-icon">P</div>

        <div class="program-output-program-info">
            <span>SELECTED PROGRAM</span>
            <h2>{{ $program->name }}</h2>
            <p>
                Program Code:
                <strong>{{ $program->code ?: 'Not assigned' }}</strong>
            </p>
        </div>

        <div class="program-output-program-status">
            <span class="program-output-status {{ $program->is_active ? 'active' : 'inactive' }}">
                {{ $program->is_active ? 'Active Program' : 'Inactive Program' }}
            </span>
        </div>
    </div>

    {{-- SUMMARY CARDS --}}
    <div class="program-output-summary">

        <div class="program-output-summary-card">
            <div class="program-output-summary-icon total">O</div>
            <div>
                <span>Total Outputs</span>
                <h3>{{ $totalOutputs }}</h3>
            </div>
        </div>

        <div class="program-output-summary-card">
            <div class="program-output-summary-icon active">A</div>
            <div>
                <span>Active Outputs</span>
                <h3>{{ $activeOutputs }}</h3>
            </div>
        </div>

        <div class="program-output-summary-card">
            <div class="program-output-summary-icon inactive">I</div>
            <div>
                <span>Inactive Outputs</span>
                <h3>{{ $inactiveOutputs }}</h3>
            </div>
        </div>

    </div>

    {{-- FLASH MESSAGE --}}
    @if(session('success'))
        <div class="program-output-alert success">
            {{ session('success') }}
        </div>
    @endif

    {{-- VALIDATION ERRORS --}}
    @if($errors->any())
        <div class="program-output-alert error">
            <strong>Please check the following:</strong>
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- OUTPUT REGISTRY --}}
    <div class="program-output-panel">

        <div class="program-output-panel-header">
            <div>
                <h2>Output Registry</h2>
                <p>All outputs registered under this program.</p>
            </div>

            <span class="program-output-record-count">
                {{ $outputs->total() }} Records
            </span>
        </div>

        {{-- SEARCH AND FILTER --}}
        <form method="GET"
              action="{{ route('programs.outputs.index', $program) }}"
              class="program-output-filters">

            <div class="program-output-search">
                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Search output code, name, or description...">
            </div>

            <select name="status">
                <option value="">All Status</option>
                <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>
                    Active
                </option>
                <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>
                    Inactive
                </option>
            </select>

            <button type="submit" class="program-output-filter-btn">
                Filter
            </button>

            @if(request()->hasAny(['search', 'status']))
                <a href="{{ route('programs.outputs.index', $program) }}"
                   class="program-output-reset-btn">
                    Reset
                </a>
            @endif
        </form>

        {{-- TABLE --}}
        <div class="program-output-table-wrapper">
            <table class="program-output-table">
                <thead>
                    <tr>
                        <th width="60">#</th>
                        <th width="150">Output Code</th>
                        <th>Output Name</th>
                        <th>Description</th>
                        <th width="120">Status</th>
                        <th width="180">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($outputs as $index => $output)
                        <tr>
                            <td>
                                {{ $outputs->firstItem() + $index }}
                            </td>

                            <td>
                                <span class="program-output-code">
                                    {{ $output->code ?: '—' }}
                                </span>
                            </td>

                            <td>
                                <strong>{{ $output->name }}</strong>
                            </td>

                            <td>
                                <span class="program-output-description">
                                    {{ $output->description ?: 'No description provided' }}
                                </span>
                            </td>

                            <td>
                                <span class="program-output-status {{ $output->is_active ? 'active' : 'inactive' }}">
                                    {{ $output->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>

                            <td>
                                <div class="program-output-actions">

                                    @can('outputs.update')
                                        <button type="button"
                                                class="program-output-action edit"
                                                onclick="editOutput(
                                                    {{ $output->id }},
                                                    {{ Js::from($output->code) }},
                                                    {{ Js::from($output->name) }},
                                                    {{ Js::from($output->description) }}
                                                )">
                                            Edit
                                        </button>

                                        <form method="POST"
                                              action="{{ route('programs.outputs.toggle-status', [$program, $output]) }}"
                                              onsubmit="return confirm('Are you sure you want to change this output status?')">
                                            @csrf

                                            <button type="submit"
                                                    class="program-output-action {{ $output->is_active ? 'deactivate' : 'activate' }}">
                                                {{ $output->is_active ? 'Deactivate' : 'Activate' }}
                                            </button>
                                        </form>
                                    @endcan

                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="program-output-empty">
                                    <div class="program-output-empty-icon">O</div>
                                    <h3>No outputs found</h3>
                                    <p>
                                        There are no outputs registered under this program yet.
                                    </p>

                                    @can('outputs.create')
                                        <button type="button"
                                                class="program-output-primary-btn"
                                                onclick="openOutputModal()">
                                            + Add First Output
                                        </button>
                                    @endcan
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="program-output-pagination">
            {{ $outputs->links() }}
        </div>

    </div>
</div>

{{-- ADD / EDIT OUTPUT MODAL --}}
<div class="program-output-modal" id="outputModal">
    <div class="program-output-modal-backdrop" onclick="closeOutputModal()"></div>

    <div class="program-output-modal-content">

        <div class="program-output-modal-header">
            <div>
                <h2 id="outputModalTitle">Add New Output</h2>
                <p>Enter the details of the program output.</p>
            </div>

            <button type="button"
                    class="program-output-modal-close"
                    onclick="closeOutputModal()">
                &times;
            </button>
        </div>

        <form id="outputForm"
              method="POST"
              action="{{ route('programs.outputs.store', $program) }}">

            @csrf

            <input type="hidden" name="_method" id="outputMethod" value="POST">

            <div class="program-output-modal-body">

                <div class="program-output-form-group">
                    <label for="outputCode">Output Code</label>
                    <input type="text"
                           id="outputCode"
                           name="code"
                           maxlength="60"
                           placeholder="e.g. OUT-001">
                    <small>Optional unique identifier for the output.</small>
                </div>

                <div class="program-output-form-group">
                    <label for="outputName">
                        Output Name <span>*</span>
                    </label>
                    <input type="text"
                           id="outputName"
                           name="name"
                           maxlength="180"
                           required
                           placeholder="Enter output name">
                </div>

                <div class="program-output-form-group">
                    <label for="outputDescription">Description</label>
                    <textarea id="outputDescription"
                              name="description"
                              rows="4"
                              placeholder="Provide a brief description of this output..."></textarea>
                </div>

                <div class="program-output-parent-info">
                    <span>Associated Program</span>
                    <strong>{{ $program->name }}</strong>
                    <small>{{ $program->code ?: 'No program code' }}</small>
                </div>

            </div>

            <div class="program-output-modal-footer">
                <button type="button"
                        class="program-output-cancel-btn"
                        onclick="closeOutputModal()">
                    Cancel
                </button>

                <button type="submit" class="program-output-primary-btn">
                    Save Output
                </button>
            </div>

        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
    const outputStoreUrl = @json(route('programs.outputs.store', $program));
    const outputUpdateUrlTemplate = @json(route('programs.outputs.update', [$program, '__OUTPUT__']));

    function openOutputModal() {
        const form = document.getElementById('outputForm');

        form.reset();
        form.action = outputStoreUrl;

        document.getElementById('outputMethod').value = 'POST';
        document.getElementById('outputModalTitle').textContent = 'Add New Output';

        document.getElementById('outputModal').classList.add('show');
    }

    function editOutput(id, code, name, description) {
        const form = document.getElementById('outputForm');

        form.action = outputUpdateUrlTemplate.replace('__OUTPUT__', id);

        document.getElementById('outputMethod').value = 'PUT';
        document.getElementById('outputCode').value = code ?? '';
        document.getElementById('outputName').value = name ?? '';
        document.getElementById('outputDescription').value = description ?? '';

        document.getElementById('outputModalTitle').textContent = 'Edit Output';
        document.getElementById('outputModal').classList.add('show');
    }

    function closeOutputModal() {
        document.getElementById('outputModal').classList.remove('show');
    }

    // Restore the modal if validation fails.
    @if($errors->any())
        document.addEventListener('DOMContentLoaded', function () {
            document.getElementById('outputCode').value = @json(old('code'));
            document.getElementById('outputName').value = @json(old('name'));
            document.getElementById('outputDescription').value = @json(old('description'));

            const method = @json(old('_method', 'POST'));

            if (method === 'PUT') {
                document.getElementById('outputModalTitle').textContent = 'Edit Output';
            }

            document.getElementById('outputMethod').value = method;
            document.getElementById('outputModal').classList.add('show');
        });
    @endif
</script>
@endpush
