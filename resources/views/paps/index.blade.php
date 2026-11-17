@extends('layouts.app')

@section('title', 'PAP Management')
@section('page-title', 'PAP Management')
@section('page-description', 'Manage Programs, Activities, and Projects')


@section('content')

<div class="pap-page">

    <div class="pap-page-header">
        <div>
            <h2>Programs, Activities, and Projects</h2>
            <p>Manage and monitor all registered PAPs across fiscal years.</p>
        </div>

         <button class="pap-btn pap-btn-primary" id="openPapModal">
            + Add New PAP
        </button>

    </div>


    {{-- Search and Filters --}}
    <div class="pap-filter-card">
        <form method="GET" action="{{ route('paps.index') }}" class="pap-filter-form">

            <div class="pap-search">
                <label for="search">Search PAP</label>
                <input type="text" id="search" name="search" value="{{ request('search') }}" placeholder="Search by name, code, or description...">
            </div>

            <div class="pap-filter">
                <label for="fiscal_year_id">Fiscal Year</label>
                <select name="fiscal_year_id" id="fiscal_year_id">
                    <option value="">All Fiscal Years</option>
                    @foreach($fiscalYears as $year)
                    <option value="{{ $year->id }}" @selected(request('fiscal_year_id')==$year->id)>
                        FY {{ $year->year }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="pap-filter">
                <label for="division_id">Division</label>
                <select name="division_id" id="division_id">
                    <option value="">All Divisions</option>
                    @foreach($divisions as $division)
                    <option value="{{ $division->id }}" @selected(request('division_id')==$division->id)>
                        {{ $division->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="pap-filter">
                <label for="status_id">Status</label>
                <select name="status_id" id="status_id">
                    <option value="">All Statuses</option>
                    @foreach($statuses as $status)
                    <option value="{{ $status->id }}" @selected(request('status_id')==$status->id)>
                        {{ $status->name }}
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="pap-filter-actions">
                <button type="submit" class="pap-btn pap-btn-primary">
                    Search
                </button>

                <a href="{{ route('paps.index') }}" class="pap-btn pap-btn-light">
                    Reset
                </a>
            </div>

        </form>
    </div>

    {{-- PAP Table --}}
    <div class="pap-table-card">

        <div class="pap-table-header">
            <div>
                <h3>PAP Directory ({{ $paps->total() }})</h3>
                <p>Registered programs, activities, and projects</p>
            </div>
        </div>

        <div class="pap-table-responsive">
            <table class="pap-table">
                <thead>
                    <tr>
                        <th>PAP INFORMATION</th>
                        <th>FISCAL YEAR</th>
                        <th>DIVISION</th>
                        <th>FOCAL PERSON</th>
                        <th>ACTIVITIES</th>
                        <th>TASKS</th>
                        <th>STATUS</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($paps as $pap)
                    <tr>
                        <td>
                            <a href="{{ route('paps.show', $pap) }}" class="pap-name">
                                {{ $pap->name }}
                            </a>

                            <div class="pap-code">
                                {{ $pap->pap_code ?: 'No PAP code assigned' }}
                            </div>
                        </td>

                        <td>
                            {{ $pap->fiscalYear?->year ?? '—' }}
                        </td>

                        <td>
                            {{ $pap->division?->name ?? 'Unassigned' }}
                        </td>

                        <td>
                            {{ $pap->focalUser?->name ?? 'Unassigned' }}
                        </td>

                        <td>
                            <span class="pap-count">
                                {{ $pap->activities_count }}
                            </span>
                        </td>

                        <td>
                            <span class="pap-count">
                                {{ $pap->tasks_count }}
                            </span>
                        </td>

                        <td>
                            <span class="pap-status">
                                {{ $pap->status?->name ?? 'Unspecified' }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7">
                            <div class="pap-empty-state">
                                <div class="pap-empty-icon">▤</div>
                                <h3>No PAP records found</h3>
                                <p>
                                    There are no records matching your search
                                    or selected filters.
                                </p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="pap-pagination">
            {{ $paps->links() }}
        </div>

    </div>

</div>

{{-- ADD PAP MODAL --}}
<div class="pap-modal-overlay" id="papModal">
    <div class="pap-modal">

        <div class="pap-modal-header">
            <div>
                <h2>Add New PAP</h2>
                <p>Enter the details of the new Program, Activity, and Project.</p>
            </div>

            <button type="button" class="pap-modal-close" id="closePapModal">
                &times;
            </button>
        </div>

        <form action="{{ route('paps.store') }}" method="POST">
            @csrf

            <div class="pap-modal-body">

                <div class="pap-form-grid">

                    <div class="form-group">
                        <label>PAP Code <span>*</span></label>
                        <input type="text" name="pap_code" value="{{ old('pap_code') }}" placeholder="e.g. BRO-2027-001" required>
                    </div>

                    <div class="form-group">
                        <label>PAP Name <span>*</span></label>
                        <input type="text" name="name" value="{{ old('name') }}" placeholder="Enter PAP name" required>
                    </div>

                    <div class="form-group">
                        <label>Fiscal Year <span>*</span></label>
                        <select name="fiscal_year_id" required>
                            <option value="">Select Fiscal Year</option>
                            @foreach($fiscalYears as $year)
                            <option value="{{ $year->id }}" @selected(old('fiscal_year_id')==$year->id)>
                                {{ $year->year }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Division <span>*</span></label>
                        <select name="division_id" required>
                            <option value="">Select Division</option>
                            @foreach($divisions as $division)
                            <option value="{{ $division->id }}" @selected(old('division_id')==$division->id)>
                                {{ $division->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Fund Source <span>*</span></label>
                        <select name="fund_source_id" required>
                            <option value="">Select Fund Source</option>
                            @foreach($fundSources as $fund)
                            <option value="{{ $fund->id }}" @selected(old('fund_source_id')==$fund->id)>
                                {{ $fund->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Program <span>*</span></label>
                        <select name="program_id" id="program_id" required>
                            <option value="">Select Program</option>

                            @foreach($programs as $program)
                            <option value="{{ $program->id }}" @selected(old('program_id')==$program->id)>
                                {{ $program->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Output Category <span>*</span></label>
                        <select name="output_category_id" id="output_category_id" required>
                            <option value="">Select Output Category</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Focal Person</label>
                        <select name="focal_user_id">
                            <option value="">Select Focal Person</option>
                            @foreach($users as $user)
                            <option value="{{ $user->id }}" @selected(old('focal_user_id')==$user->id)>
                                {{ $user->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Status <span>*</span></label>
                        <select name="status_id" required>
                            <option value="">Select Status</option>
                            @foreach($statuses as $status)
                            <option value="{{ $status->id }}" @selected(old('status_id')==$status->id)>
                                {{ $status->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Start Date</label>
                        <input type="date" name="start_date" value="{{ old('start_date') }}">
                    </div>

                    <div class="form-group">
                        <label>End Date</label>
                        <input type="date" name="end_date" value="{{ old('end_date') }}">
                    </div>

                    <div class="form-group full-width">
                        <label>Description</label>
                        <textarea name="description" rows="3" placeholder="Enter PAP description">{{ old('description') }}</textarea>
                    </div>

                </div>
            </div>

            <div class="pap-modal-footer">
                <button type="button" class="btn btn-secondary" id="cancelPapModal">
                    Cancel
                </button>

                <button type="submit" class="btn btn-primary">
                    Save PAP
                </button>
            </div>
        </form>

    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('papModal');

        const openBtn = document.getElementById('openPapModal');
        const closeBtn = document.getElementById('closePapModal');
        const cancelBtn = document.getElementById('cancelPapModal');

        function openModal() {
            modal.classList.add('active');
        }

        function closeModal() {
            modal.classList.remove('active');
        }

        openBtn ?.addEventListener('click', openModal);
        closeBtn ?.addEventListener('click', closeModal);
        cancelBtn ?.addEventListener('click', closeModal);

        modal ?.addEventListener('click', function(event) {
            if (event.target === modal) {
                closeModal();
            }
        });

        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeModal();
            }
        });

        // Reopen modal if Laravel validation returns errors
        @if($errors -> any())
        openModal();
        @endif

        @if(session('success')) <
            div class = "alert alert-success" > {
                {
                    session('success')
                }
            }
        </div>
        @endif
    });

    document.addEventListener('DOMContentLoaded', function() {

        const programSelect = document.getElementById('program_id');
        const outputSelect = document.getElementById('output_category_id');

        // Get all output categories from Laravel
        const allOutputs = @json($outputCategories);

        // Previously selected output (useful after validation errors)
        const oldOutputId = @json(old('output_category_id'));

        function filterOutputs(programId, selectedOutputId = '') {

            outputSelect.innerHTML =
                '<option value="">Select Output Category</option>';

            if (!programId) {
                return;
            }

            const filteredOutputs = allOutputs.filter(output =>
                String(output.program_id) === String(programId) &&
                Number(output.is_active) === 1
            );

            if (filteredOutputs.length === 0) {
                outputSelect.innerHTML =
                    '<option value="">No outputs available</option>';
                return;
            }

            filteredOutputs.forEach(output => {

                const option = document.createElement('option');

                option.value = output.id;
                option.textContent = output.code ?
                    output.code + ' - ' + output.name :
                    output.name;

                if (String(output.id) === String(selectedOutputId)) {
                    option.selected = true;
                }

                outputSelect.appendChild(option);
            });
        }

        // Update outputs when program changes
        programSelect.addEventListener('change', function() {
            filterOutputs(this.value);
        });

        // Load outputs for initially selected program
        filterOutputs(programSelect.value, oldOutputId);

    });

</script>

@endsection
