
@extends('layouts.app')

@section('title', 'Organizational Management')

@section('content')
<div class="page-content">

    {{-- PAGE HEADER --}}
    <div class="page-header">
        <div>
            <h1>Organizational Management</h1>
            <p>Manage offices, divisions, organizational units, and employee positions.</p>
        </div>
    </div>

    {{-- ALERTS --}}
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <strong>Please check the following:</strong>
            <ul style="margin:8px 0 0 18px;">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- SUMMARY CARDS --}}
    <div class="role-summary-grid">
        <div class="role-summary-card">
            <span class="summary-label">Offices</span>
            <strong class="summary-value">{{ $counts['offices'] }}</strong>
            <small class="text-muted">{{ $activeCounts['offices'] }} active</small>
        </div>

        <div class="role-summary-card">
            <span class="summary-label">Divisions</span>
            <strong class="summary-value">{{ $counts['divisions'] }}</strong>
            <small class="text-muted">{{ $activeCounts['divisions'] }} active</small>
        </div>

        <div class="role-summary-card">
            <span class="summary-label">Organizational Units</span>
            <strong class="summary-value">{{ $counts['units'] }}</strong>
            <small class="text-muted">{{ $activeCounts['units'] }} active</small>
        </div>

        <div class="role-summary-card">
            <span class="summary-label">Positions</span>
            <strong class="summary-value">{{ $counts['positions'] }}</strong>
            <small class="text-muted">{{ $activeCounts['positions'] }} active</small>
        </div>
    </div>

    {{-- SEARCH AND FILTER --}}
    <div class="card" style="margin-bottom:20px;">
        <div class="card-body">
            <form method="GET" action="{{ route('organization.index') }}" class="filter-bar">
                <div class="search-box">
                    <span class="search-icon">⌕</span>
                    <input
                        type="text"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Search organizational records..."
                    >
                </div>

                <select name="status" class="form-control" style="width:160px;">
                    <option value="all" {{ $status === 'all' ? 'selected' : '' }}>All Status</option>
                    <option value="active" {{ $status === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ $status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>

                <button type="submit" class="btn btn-primary">Search</button>
                <a href="{{ route('organization.index') }}" class="btn btn-secondary">Reset</a>
            </form>
        </div>
    </div>

    {{-- TABS --}}
    <div class="org-tabs">
        <button type="button" class="org-tab active" data-tab="offices">
            Offices <span>{{ $counts['offices'] }}</span>
        </button>

        <button type="button" class="org-tab" data-tab="divisions">
            Divisions <span>{{ $counts['divisions'] }}</span>
        </button>

        <button type="button" class="org-tab" data-tab="units">
            Organizational Units <span>{{ $counts['units'] }}</span>
        </button>

        <button type="button" class="org-tab" data-tab="positions">
            Positions <span>{{ $counts['positions'] }}</span>
        </button>
    </div>

    {{-- ====================================================== --}}
    {{-- OFFICES TAB --}}
    {{-- ====================================================== --}}
    <section class="org-panel active" id="panel-offices">
        <div class="card">
            <div class="card-header">
                <div>
                    <h3>Offices</h3>
                    <small class="text-muted">Manage the main offices of BRO.</small>
                </div>

                @can('organization-management.create')
                    <button type="button" class="btn btn-primary btn-sm"
                        onclick="openOfficeModal()">
                        + Add Office
                    </button>
                @endcan
            </div>

            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Office Name</th>
                            <th>Code</th>
                            <th>Divisions</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($offices as $office)
                            <tr>
                                <td>
                                    <strong>{{ $office->name }}</strong>
                                    @if($office->description)
                                        <small class="text-muted" style="display:block;">
                                            {{ $office->description }}
                                        </small>
                                    @endif
                                </td>
                                <td>{{ $office->code ?: '—' }}</td>
                                <td>{{ $office->divisions_count }}</td>
                                <td>
                                    <span class="badge {{ $office->is_active ? 'badge-success' : 'badge-secondary' }}">
                                        {{ $office->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="table-actions">
                                        @can('organization-management.update')
                                            <button type="button" class="btn btn-secondary btn-sm"
                                                onclick="editOfficeModal({{ $office->id }}, {{ Illuminate\Support\Js::from([
                                                    'name' => $office->name,
                                                    'code' => $office->code,
                                                    'description' => $office->description,
                                                ]) }})">
                                                Edit
                                            </button>

                                            <form method="POST"
                                                action="{{ route('organization.status', ['type' => 'office', 'id' => $office->id]) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit"
                                                    class="btn btn-sm {{ $office->is_active ? 'btn-danger' : 'btn-success' }}"
                                                    onclick="return confirm('Change this office status?')">
                                                    {{ $office->is_active ? 'Deactivate' : 'Activate' }}
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">
                                    <div class="empty-state">
                                        <strong>No offices found</strong>
                                        <span>Add your first office to get started.</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="card-body">
                {{ $offices->links() }}
            </div>
        </div>
    </section>

    {{-- ====================================================== --}}
    {{-- DIVISIONS TAB --}}
    {{-- ====================================================== --}}
    <section class="org-panel" id="panel-divisions">
        <div class="card">
            <div class="card-header">
                <div>
                    <h3>Divisions</h3>
                    <small class="text-muted">Manage divisions and their parent offices.</small>
                </div>

                @can('organization-management.create')
                    <button type="button" class="btn btn-primary btn-sm"
                        onclick="openDivisionModal()">
                        + Add Division
                    </button>
                @endcan
            </div>

            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Division Name</th>
                            <th>Code</th>
                            <th>Office</th>
                            <th>Units</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($divisions as $division)
                            <tr>
                                <td>
                                    <strong>{{ $division->name }}</strong>
                                    @if($division->description)
                                        <small class="text-muted" style="display:block;">
                                            {{ $division->description }}
                                        </small>
                                    @endif
                                </td>
                                <td>{{ $division->code ?: '—' }}</td>
                                <td>{{ $division->office?->name ?? 'Unassigned' }}</td>
                                <td>{{ $division->organizational_units_count }}</td>
                                <td>
                                    <span class="badge {{ $division->is_active ? 'badge-success' : 'badge-secondary' }}">
                                        {{ $division->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="table-actions">
                                        @can('organization-management.update')
                                            <button type="button" class="btn btn-secondary btn-sm"
                                                onclick="editDivisionModal({{ $division->id }}, {{ Illuminate\Support\Js::from([
                                                    'name' => $division->name,
                                                    'code' => $division->code,
                                                    'description' => $division->description,
                                                    'office_id' => $division->office_id,
                                                ]) }})">
                                                Edit
                                            </button>

                                            <form method="POST"
                                                action="{{ route('organization.status', ['type' => 'division', 'id' => $division->id]) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit"
                                                    class="btn btn-sm {{ $division->is_active ? 'btn-danger' : 'btn-success' }}"
                                                    onclick="return confirm('Change this division status?')">
                                                    {{ $division->is_active ? 'Deactivate' : 'Activate' }}
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6">
                                    <div class="empty-state">
                                        <strong>No divisions found</strong>
                                        <span>Add your first division to get started.</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="card-body">
                {{ $divisions->links() }}
            </div>
        </div>
    </section>

    {{-- ====================================================== --}}
    {{-- ORGANIZATIONAL UNITS TAB --}}
    {{-- ====================================================== --}}
    <section class="org-panel" id="panel-units">
        <div class="card">
            <div class="card-header">
                <div>
                    <h3>Organizational Units</h3>
                    <small class="text-muted">Manage units within divisions and parent units.</small>
                </div>

                @can('organization-management.create')
                    <button type="button" class="btn btn-primary btn-sm"
                        onclick="openUnitModal()">
                        + Add Unit
                    </button>
                @endcan
            </div>

            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Unit Name</th>
                            <th>Unit Type</th>
                            <th>Division</th>
                            <th>Parent Unit</th>
                            <th>Sub-units</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($units as $unit)
                            <tr>
                                <td>
                                    <strong>{{ $unit->name }}</strong>
                                    @if($unit->description)
                                        <small class="text-muted" style="display:block;">
                                            {{ $unit->description }}
                                        </small>
                                    @endif
                                </td>
                                <td>{{ $unit->unit_type ?: '—' }}</td>
                                <td>{{ $unit->division?->name ?? 'Unassigned' }}</td>
                                <td>{{ $unit->parent?->name ?? '—' }}</td>
                                <td>{{ $unit->children_count }}</td>
                                <td>
                                    <span class="badge {{ $unit->is_active ? 'badge-success' : 'badge-secondary' }}">
                                        {{ $unit->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="table-actions">
                                        @can('organization-management.update')
                                            <button type="button" class="btn btn-secondary btn-sm"
                                                onclick="editUnitModal({{ $unit->id }}, {{ Illuminate\Support\Js::from([
                                                    'name' => $unit->name,
                                                    'unit_type' => $unit->unit_type,
                                                    'description' => $unit->description,
                                                    'division_id' => $unit->division_id,
                                                    'parent_id' => $unit->parent_id,
                                                ]) }})">
                                                Edit
                                            </button>

                                            <form method="POST"
                                                action="{{ route('organization.status', ['type' => 'unit', 'id' => $unit->id]) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit"
                                                    class="btn btn-sm {{ $unit->is_active ? 'btn-danger' : 'btn-success' }}"
                                                    onclick="return confirm('Change this unit status?')">
                                                    {{ $unit->is_active ? 'Deactivate' : 'Activate' }}
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <div class="empty-state">
                                        <strong>No organizational units found</strong>
                                        <span>Add your first organizational unit to get started.</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="card-body">
                {{ $units->links() }}
            </div>
        </div>
    </section>

    {{-- ====================================================== --}}
    {{-- POSITIONS TAB --}}
    {{-- ====================================================== --}}
    <section class="org-panel" id="panel-positions">
        <div class="card">
            <div class="card-header">
                <div>
                    <h3>Positions</h3>
                    <small class="text-muted">Manage employee position titles and salary grades.</small>
                </div>

                @can('organization-management.create')
                    <button type="button" class="btn btn-primary btn-sm"
                        onclick="openPositionModal()">
                        + Add Position
                    </button>
                @endcan
            </div>

            <div class="table-container">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Position Title</th>
                            <th>Salary Grade</th>
                            <th>Assigned Users</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($positions as $position)
                            <tr>
                                <td><strong>{{ $position->title }}</strong></td>
                                <td>{{ $position->salary_grade ?: '—' }}</td>
                                <td>{{ $position->users_count }}</td>
                                <td>
                                    <span class="badge {{ $position->is_active ? 'badge-success' : 'badge-secondary' }}">
                                        {{ $position->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="table-actions">
                                        @can('organization-management.update')
                                            <button type="button" class="btn btn-secondary btn-sm"
                                                onclick="editPositionModal({{ $position->id }}, {{ Illuminate\Support\Js::from([
                                                    'title' => $position->title,
                                                    'salary_grade' => $position->salary_grade,
                                                ]) }})">
                                                Edit
                                            </button>

                                            <form method="POST"
                                                action="{{ route('organization.status', ['type' => 'position', 'id' => $position->id]) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit"
                                                    class="btn btn-sm {{ $position->is_active ? 'btn-danger' : 'btn-success' }}"
                                                    onclick="return confirm('Change this position status?')">
                                                    {{ $position->is_active ? 'Deactivate' : 'Activate' }}
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">
                                    <div class="empty-state">
                                        <strong>No positions found</strong>
                                        <span>Add your first position to get started.</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="card-body">
                {{ $positions->links() }}
            </div>
        </div>
    </section>

</div>


{{-- ========================================================== --}}
{{-- OFFICE ADD / EDIT MODAL --}}
{{-- ========================================================== --}}
<div class="modal-overlay" id="officeModal">
    <div class="modal org-modal">
        <form method="POST" id="officeForm" action="{{ route('organization.offices.store') }}">
            @csrf
            <input type="hidden" name="_method" id="officeMethod" value="POST">

            <div class="modal-header">
                <div>
                    <h2 id="officeModalTitle">Add Office</h2>
                    <small class="text-muted">Enter office information.</small>
                </div>
                <button type="button" class="modal-close" onclick="closeOfficeModal()">&times;</button>
            </div>

            <div class="modal-body">
                <div class="form-group">
                    <label for="office_name">Office Name <span class="required">*</span></label>
                    <input type="text" name="name" id="office_name" maxlength="180" required>
                </div>

                <div class="form-group">
                    <label for="office_code">Office Code</label>
                    <input type="text" name="code" id="office_code" maxlength="50">
                </div>

                <div class="form-group">
                    <label for="office_description">Description</label>
                    <textarea name="description" id="office_description" rows="3"></textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeOfficeModal()">Cancel</button>
                <button type="submit" class="btn btn-primary" id="officeSubmitButton">Save Office</button>
            </div>
        </form>
    </div>
</div>


{{-- ========================================================== --}}
{{-- DIVISION ADD / EDIT MODAL --}}
{{-- ========================================================== --}}
<div class="modal-overlay" id="divisionModal">
    <div class="modal org-modal">
        <form method="POST" id="divisionForm" action="{{ route('organization.divisions.store') }}">
            @csrf
            <input type="hidden" name="_method" id="divisionMethod" value="POST">

            <div class="modal-header">
                <div>
                    <h2 id="divisionModalTitle">Add Division</h2>
                    <small class="text-muted">Enter division information.</small>
                </div>
                <button type="button" class="modal-close" onclick="closeDivisionModal()">&times;</button>
            </div>

            <div class="modal-body">
                <div class="form-group">
                    <label for="division_name">Division Name <span class="required">*</span></label>
                    <input type="text" name="name" id="division_name" maxlength="180" required>
                </div>

                <div class="form-group">
                    <label for="division_code">Division Code</label>
                    <input type="text" name="code" id="division_code" maxlength="50">
                </div>

                <div class="form-group">
                    <label for="division_office">Parent Office</label>
                    <select name="office_id" id="division_office">
                        <option value="">Select Office</option>
                        @foreach($officeOptions as $office)
                            <option value="{{ $office->id }}">{{ $office->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="division_description">Description</label>
                    <textarea name="description" id="division_description" rows="3"></textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeDivisionModal()">Cancel</button>
                <button type="submit" class="btn btn-primary" id="divisionSubmitButton">Save Division</button>
            </div>
        </form>
    </div>
</div>


{{-- ========================================================== --}}
{{-- ORGANIZATIONAL UNIT ADD / EDIT MODAL --}}
{{-- ========================================================== --}}
<div class="modal-overlay" id="unitModal">
    <div class="modal org-modal">
        <form method="POST" id="unitForm" action="{{ route('organization.units.store') }}">
            @csrf
            <input type="hidden" name="_method" id="unitMethod" value="POST">

            <div class="modal-header">
                <div>
                    <h2 id="unitModalTitle">Add Organizational Unit</h2>
                    <small class="text-muted">Define the organizational unit and its hierarchy.</small>
                </div>
                <button type="button" class="modal-close" onclick="closeUnitModal()">&times;</button>
            </div>

            <div class="modal-body">
                <div class="form-group">
                    <label for="unit_name">Unit Name <span class="required">*</span></label>
                    <input type="text" name="name" id="unit_name" maxlength="180" required>
                </div>

                <div class="form-group">
                    <label for="unit_type">Unit Type</label>
                    <input type="text" name="unit_type" id="unit_type" maxlength="80"
                        placeholder="e.g. Section, Unit, Team">
                </div>

                <div class="form-group">
                    <label for="unit_division">Division</label>
                    <select name="division_id" id="unit_division">
                        <option value="">Select Division</option>
                        @foreach($divisionOptions as $division)
                            <option value="{{ $division->id }}">{{ $division->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="unit_parent">Parent Unit</label>
                    <select name="parent_id" id="unit_parent">
                        <option value="">No Parent Unit</option>
                        @foreach($unitOptions as $unitOption)
                            <option value="{{ $unitOption->id }}">{{ $unitOption->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label for="unit_description">Description</label>
                    <textarea name="description" id="unit_description" rows="3"></textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeUnitModal()">Cancel</button>
                <button type="submit" class="btn btn-primary" id="unitSubmitButton">Save Unit</button>
            </div>
        </form>
    </div>
</div>


{{-- ========================================================== --}}
{{-- POSITION ADD / EDIT MODAL --}}
{{-- ========================================================== --}}
<div class="modal-overlay" id="positionModal">
    <div class="modal org-modal">
        <form method="POST" id="positionForm" action="{{ route('organization.positions.store') }}">
            @csrf
            <input type="hidden" name="_method" id="positionMethod" value="POST">

            <div class="modal-header">
                <div>
                    <h2 id="positionModalTitle">Add Position</h2>
                    <small class="text-muted">Enter employee position information.</small>
                </div>
                <button type="button" class="modal-close" onclick="closePositionModal()">&times;</button>
            </div>

            <div class="modal-body">
                <div class="form-group">
                    <label for="position_title">Position Title <span class="required">*</span></label>
                    <input type="text" name="title" id="position_title" maxlength="180" required>
                </div>

                <div class="form-group">
                    <label for="position_salary_grade">Salary Grade</label>
                    <input type="text" name="salary_grade" id="position_salary_grade"
                        maxlength="30" placeholder="e.g. SG 24">
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closePositionModal()">Cancel</button>
                <button type="submit" class="btn btn-primary" id="positionSubmitButton">Save Position</button>
            </div>
        </form>
    </div>
</div>

@endsection


@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // =========================================================
    // TAB HANDLING
    // =========================================================

    const tabs = document.querySelectorAll('.org-tab');
    const panels = document.querySelectorAll('.org-panel');

    function showTab(tabName) {
        tabs.forEach(tab => {
            tab.classList.toggle('active', tab.dataset.tab === tabName);
        });

        panels.forEach(panel => {
            panel.classList.toggle('active', panel.id === 'panel-' + tabName);
        });

        sessionStorage.setItem('orgActiveTab', tabName);
    }

    tabs.forEach(tab => {
        tab.addEventListener('click', function () {
            showTab(this.dataset.tab);
        });
    });

    const savedTab = sessionStorage.getItem('orgActiveTab');

    if (savedTab && document.getElementById('panel-' + savedTab)) {
        showTab(savedTab);
    }


    // =========================================================
    // GENERIC MODAL HELPERS
    // Each tab still has its own independent form and modal.
    // =========================================================

    function openModal(modalId) {
        const modal = document.getElementById(modalId);

        if (!modal) return;

        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
    }

    function closeModal(modalId) {
        const modal = document.getElementById(modalId);

        if (!modal) return;

        modal.classList.remove('show');
        document.body.style.overflow = '';
    }

    function prepareAddForm(formId, methodId, titleId, buttonId, action, title, buttonText) {
        const form = document.getElementById(formId);

        form.reset();
        form.action = action;

        document.getElementById(methodId).value = 'POST';
        document.getElementById(titleId).textContent = title;
        document.getElementById(buttonId).textContent = buttonText;
    }

    function prepareEditForm(formId, methodId, titleId, buttonId, action, title, buttonText, data, fieldMap) {
        const form = document.getElementById(formId);

        form.reset();
        form.action = action;

        document.getElementById(methodId).value = 'PUT';
        document.getElementById(titleId).textContent = title;
        document.getElementById(buttonId).textContent = buttonText;

        Object.entries(data).forEach(([key, value]) => {
            const fieldId = fieldMap[key];

            if (fieldId) {
                const field = document.getElementById(fieldId);

                if (field) {
                    field.value = value ?? '';
                }
            }
        });
    }


    // =========================================================
    // OFFICE MODAL
    // =========================================================

    const officeStoreUrl = @json(route('organization.offices.store'));
    const officeUpdateBaseUrl = @json(url('/organizational-management/offices'));

    const officeFieldMap = {
        name: 'office_name',
        code: 'office_code',
        description: 'office_description'
    };

    window.openOfficeModal = function () {
        prepareAddForm(
            'officeForm',
            'officeMethod',
            'officeModalTitle',
            'officeSubmitButton',
            officeStoreUrl,
            'Add Office',
            'Save Office'
        );

        openModal('officeModal');
    };

    window.editOfficeModal = function (id, data) {
        prepareEditForm(
            'officeForm',
            'officeMethod',
            'officeModalTitle',
            'officeSubmitButton',
            officeUpdateBaseUrl + '/' + id,
            'Edit Office',
            'Update Office',
            data,
            officeFieldMap
        );

        openModal('officeModal');
    };

    window.closeOfficeModal = function () {
        closeModal('officeModal');
    };


    // =========================================================
    // DIVISION MODAL
    // =========================================================

    const divisionStoreUrl = @json(route('organization.divisions.store'));
    const divisionUpdateBaseUrl = @json(url('/organizational-management/divisions'));

    const divisionFieldMap = {
        name: 'division_name',
        code: 'division_code',
        office_id: 'division_office',
        description: 'division_description'
    };

    window.openDivisionModal = function () {
        prepareAddForm(
            'divisionForm',
            'divisionMethod',
            'divisionModalTitle',
            'divisionSubmitButton',
            divisionStoreUrl,
            'Add Division',
            'Save Division'
        );

        openModal('divisionModal');
    };

    window.editDivisionModal = function (id, data) {
        prepareEditForm(
            'divisionForm',
            'divisionMethod',
            'divisionModalTitle',
            'divisionSubmitButton',
            divisionUpdateBaseUrl + '/' + id,
            'Edit Division',
            'Update Division',
            data,
            divisionFieldMap
        );

        openModal('divisionModal');
    };

    window.closeDivisionModal = function () {
        closeModal('divisionModal');
    };


    // =========================================================
    // ORGANIZATIONAL UNIT MODAL
    // =========================================================

    const unitStoreUrl = @json(route('organization.units.store'));
    const unitUpdateBaseUrl = @json(url('/organizational-management/units'));

    const unitFieldMap = {
        name: 'unit_name',
        unit_type: 'unit_type',
        division_id: 'unit_division',
        parent_id: 'unit_parent',
        description: 'unit_description'
    };

    window.openUnitModal = function () {
        prepareAddForm(
            'unitForm',
            'unitMethod',
            'unitModalTitle',
            'unitSubmitButton',
            unitStoreUrl,
            'Add Organizational Unit',
            'Save Unit'
        );

        openModal('unitModal');
    };

    window.editUnitModal = function (id, data) {
        prepareEditForm(
            'unitForm',
            'unitMethod',
            'unitModalTitle',
            'unitSubmitButton',
            unitUpdateBaseUrl + '/' + id,
            'Edit Organizational Unit',
            'Update Unit',
            data,
            unitFieldMap
        );

        openModal('unitModal');
    };

    window.closeUnitModal = function () {
        closeModal('unitModal');
    };


    // =========================================================
    // POSITION MODAL
    // =========================================================

    const positionStoreUrl = @json(route('organization.positions.store'));
    const positionUpdateBaseUrl = @json(url('/organizational-management/positions'));

    const positionFieldMap = {
        title: 'position_title',
        salary_grade: 'position_salary_grade'
    };

    window.openPositionModal = function () {
        prepareAddForm(
            'positionForm',
            'positionMethod',
            'positionModalTitle',
            'positionSubmitButton',
            positionStoreUrl,
            'Add Position',
            'Save Position'
        );

        openModal('positionModal');
    };

    window.editPositionModal = function (id, data) {
        prepareEditForm(
            'positionForm',
            'positionMethod',
            'positionModalTitle',
            'positionSubmitButton',
            positionUpdateBaseUrl + '/' + id,
            'Edit Position',
            'Update Position',
            data,
            positionFieldMap
        );

        openModal('positionModal');
    };

    window.closePositionModal = function () {
        closeModal('positionModal');
    };


    // =========================================================
    // CLOSE MODALS WHEN CLICKING OUTSIDE
    // =========================================================

    [
        'officeModal',
        'divisionModal',
        'unitModal',
        'positionModal'
    ].forEach(modalId => {
        const modal = document.getElementById(modalId);

        modal.addEventListener('click', function (event) {
            if (event.target === modal) {
                closeModal(modalId);
            }
        });
    });


    // =========================================================
    // ESCAPE KEY
    // =========================================================

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            [
                'officeModal',
                'divisionModal',
                'unitModal',
                'positionModal'
            ].forEach(closeModal);
        }
    });

});
</script>
@endpush
