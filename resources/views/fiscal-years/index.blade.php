@extends('layouts.app')

@section('content')
<div class="fy-page">

    {{-- PAGE HEADER --}}
    <div class="fy-header">
        <div>
            <div class="fy-eyebrow">SYSTEM CONFIGURATION</div>
            <h1>Fiscal Year Management</h1>
            <p>Manage the fiscal years used across BRO360.</p>
        </div>

        @can('fiscal-year-management.create')
            <button type="button" class="fy-primary-btn" onclick="openFiscalYearModal()">
                <span>＋</span> Add Fiscal Year
            </button>
        @endcan
    </div>

    {{-- ALERTS --}}
    @if(session('success'))
        <div class="fy-alert fy-alert-success">
            {{ session('success') }}
        </div>
    @endif

    @if(session('info'))
        <div class="fy-alert fy-alert-info">
            {{ session('info') }}
        </div>
    @endif

    @if($errors->any())
        <div class="fy-alert fy-alert-error">
            <strong>Please check the following:</strong>
            <ul>
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- CURRENT ACTIVE FISCAL YEAR --}}
    <div class="fy-current-card">
        <div class="fy-current-icon">📅</div>
        <div class="fy-current-info">
            <span class="fy-current-label">CURRENT ACTIVE FISCAL YEAR</span>

            @if($activeFiscalYear)
                <h2>FY {{ $activeFiscalYear->year }}</h2>
                <p>
                    {{ $activeFiscalYear->start_date->format('F d, Y') }}
                    – 
                    {{ $activeFiscalYear->end_date->format('F d, Y') }}
                </p>
            @else
                <h2>No Active Fiscal Year</h2>
                <p>Select a fiscal year below and activate it.</p>
            @endif
        </div>

        <div class="fy-current-status">
            <span class="fy-status-dot"></span>
            {{ $activeFiscalYear ? 'Active' : 'Not Set' }}
        </div>
    </div>

    {{-- SUMMARY CARDS --}}
    <div class="fy-summary-grid">

        <div class="fy-summary-card">
            <div class="fy-summary-top">
                <span>Total Fiscal Years</span>
                <span class="fy-summary-icon">▤</span>
            </div>
            <strong>{{ $counts['total'] }}</strong>
            <small>All registered fiscal years</small>
        </div>

        <div class="fy-summary-card">
            <div class="fy-summary-top">
                <span>Draft</span>
                <span class="fy-summary-icon">◷</span>
            </div>
            <strong>{{ $counts['draft'] }}</strong>
            <small>Not yet activated</small>
        </div>

        <div class="fy-summary-card">
            <div class="fy-summary-top">
                <span>Active</span>
                <span class="fy-summary-icon">●</span>
            </div>
            <strong>{{ $counts['active'] }}</strong>
            <small>Currently in use</small>
        </div>

        <div class="fy-summary-card">
            <div class="fy-summary-top">
                <span>Closed</span>
                <span class="fy-summary-icon">✓</span>
            </div>
            <strong>{{ $counts['closed'] }}</strong>
            <small>Completed fiscal years</small>
        </div>

    </div>

    {{-- FISCAL YEAR DIRECTORY --}}
    <div class="fy-panel">

        <div class="fy-panel-header">
            <div>
                <h3>Fiscal Year Directory</h3>
                <p>View and manage registered fiscal years.</p>
            </div>
            <span class="fy-record-count">
                {{ $fiscalYears->total() }} records
            </span>
        </div>

        {{-- SEARCH AND FILTER --}}
        <form action="{{ route('fiscal-years.index') }}" method="GET" class="fy-toolbar">

            <div class="fy-search">
                <span>⌕</span>
                <input
                    type="text"
                    name="search"
                    value="{{ request('search') }}"
                    placeholder="Search fiscal year..."
                >
            </div>

            <select name="status" class="fy-filter">
                <option value="all">All Statuses</option>
                <option value="Draft" {{ request('status') === 'Draft' ? 'selected' : '' }}>Draft</option>
                <option value="Active" {{ request('status') === 'Active' ? 'selected' : '' }}>Active</option>
                <option value="Closed" {{ request('status') === 'Closed' ? 'selected' : '' }}>Closed</option>
            </select>

            <button type="submit" class="fy-filter-btn">Apply Filter</button>

            @if(request()->filled('search') || (request()->filled('status') && request('status') !== 'all'))
                <a href="{{ route('fiscal-years.index') }}" class="fy-reset-btn">Reset</a>
            @endif

        </form>

        {{-- TABLE --}}
        <div class="fy-table-wrap">
            <table class="fy-table">
                <thead>
                    <tr>
                        <th>FISCAL YEAR</th>
                        <th>START DATE</th>
                        <th>END DATE</th>
                        <th>PAP RECORDS</th>
                        <th>STATUS</th>
                        <th class="fy-action-heading">ACTIONS</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($fiscalYears as $fiscalYear)
                        <tr>
                            <td>
                                <div class="fy-year-cell">
                                    <div class="fy-year-icon">FY</div>
                                    <div>
                                        <strong>FY {{ $fiscalYear->year }}</strong>
                                        @if($fiscalYear->status === 'Active')
                                            <small>Current fiscal year</small>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            <td>{{ $fiscalYear->start_date->format('M d, Y') }}</td>

                            <td>{{ $fiscalYear->end_date->format('M d, Y') }}</td>

                            <td>
                                <span class="fy-pap-count">
                                    {{ number_format($fiscalYear->paps_count) }}
                                </span>
                            </td>

                            <td>
                                <span class="fy-badge fy-badge-{{ strtolower($fiscalYear->status) }}">
                                    {{ $fiscalYear->status }}
                                </span>
                            </td>

                            <td>
                                <div class="fy-actions">

                                    @can('fiscal-year-management.update')
                                        <button
                                            type="button"
                                            class="fy-action-btn"
                                            title="Edit"
                                            onclick="editFiscalYear(
                                                {{ $fiscalYear->id }},
                                                {{ $fiscalYear->year }},
                                                '{{ $fiscalYear->start_date->format('Y-m-d') }}',
                                                '{{ $fiscalYear->end_date->format('Y-m-d') }}'
                                            )"
                                        >
                                            Edit
                                        </button>

                                        @if($fiscalYear->status !== 'Active' && $fiscalYear->status !== 'Closed')
                                            <form
                                                action="{{ route('fiscal-years.activate', $fiscalYear) }}"
                                                method="POST"
                                                onsubmit="return confirm('Activate FY {{ $fiscalYear->year }}? Any currently active fiscal year will be closed.')"
                                            >
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="fy-action-btn fy-action-activate">
                                                    Activate
                                                </button>
                                            </form>
                                        @endif

                                        @if($fiscalYear->status !== 'Closed')
                                            <form
                                                action="{{ route('fiscal-years.close', $fiscalYear) }}"
                                                method="POST"
                                                onsubmit="return confirm('Are you sure you want to close FY {{ $fiscalYear->year }}?')"
                                            >
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="fy-action-btn fy-action-close">
                                                    Close
                                                </button>
                                            </form>
                                        @endif
                                    @endcan

                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="fy-empty">
                                    <div class="fy-empty-icon">📅</div>
                                    <strong>No fiscal years found</strong>
                                    <p>Add a fiscal year or change your search filters.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="fy-pagination">
            {{ $fiscalYears->links() }}
        </div>

    </div>
</div>

{{-- ADD / EDIT MODAL --}}
<div class="fy-modal-overlay" id="fiscalYearModal">
    <div class="fy-modal">

        <div class="fy-modal-header">
            <div>
                <span class="fy-eyebrow">FISCAL YEAR CONFIGURATION</span>
                <h3 id="fiscalYearModalTitle">Add Fiscal Year</h3>
                <p id="fiscalYearModalDescription">Register a new fiscal year.</p>
            </div>

            <button type="button" class="fy-modal-close" onclick="closeFiscalYearModal()">
                &times;
            </button>
        </div>

        <form id="fiscalYearForm" method="POST" action="{{ route('fiscal-years.store') }}">
            @csrf

            <input type="hidden" name="_method" id="fiscalYearMethod" value="POST">

            <div class="fy-modal-body">

                <div class="fy-form-group">
                    <label for="fy_year">Fiscal Year <span>*</span></label>
                    <input
                        type="number"
                        id="fy_year"
                        name="year"
                        min="2000"
                        max="2100"
                        placeholder="e.g. 2027"
                        required
                    >
                    <small>Enter the fiscal year number.</small>
                </div>

                <div class="fy-form-row">
                    <div class="fy-form-group">
                        <label for="fy_start_date">Start Date <span>*</span></label>
                        <input type="date" id="fy_start_date" name="start_date" required>
                    </div>

                    <div class="fy-form-group">
                        <label for="fy_end_date">End Date <span>*</span></label>
                        <input type="date" id="fy_end_date" name="end_date" required>
                    </div>
                </div>

                <div class="fy-form-note">
                    <strong>Note:</strong> Newly created fiscal years are saved as Draft.
                    You can activate them from the directory.
                </div>

            </div>

            <div class="fy-modal-footer">
                <button type="button" class="fy-secondary-btn" onclick="closeFiscalYearModal()">
                    Cancel
                </button>
                <button type="submit" class="fy-primary-btn" id="fiscalYearSubmitBtn">
                    Save Fiscal Year
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const fiscalYearStoreUrl = @json(route('fiscal-years.store'));
    const fiscalYearUpdateUrl = @json(route('fiscal-years.update', ['fiscalYear' => '__ID__']));

    function openFiscalYearModal() {
        const form = document.getElementById('fiscalYearForm');

        form.reset();
        form.action = fiscalYearStoreUrl;

        document.getElementById('fiscalYearMethod').value = 'POST';
        document.getElementById('fiscalYearModalTitle').textContent = 'Add Fiscal Year';
        document.getElementById('fiscalYearModalDescription').textContent = 'Register a new fiscal year.';
        document.getElementById('fiscalYearSubmitBtn').textContent = 'Save Fiscal Year';

        document.getElementById('fiscalYearModal').classList.add('show');
    }

    function editFiscalYear(id, year, startDate, endDate) {
        const form = document.getElementById('fiscalYearForm');

        form.action = fiscalYearUpdateUrl.replace('__ID__', id);

        document.getElementById('fiscalYearMethod').value = 'PUT';
        document.getElementById('fiscalYearModalTitle').textContent = 'Edit Fiscal Year';
        document.getElementById('fiscalYearModalDescription').textContent = 'Update fiscal year information.';
        document.getElementById('fiscalYearSubmitBtn').textContent = 'Update Fiscal Year';

        document.getElementById('fy_year').value = year;
        document.getElementById('fy_start_date').value = startDate;
        document.getElementById('fy_end_date').value = endDate;

        document.getElementById('fiscalYearModal').classList.add('show');
    }

    function closeFiscalYearModal() {
        document.getElementById('fiscalYearModal').classList.remove('show');
    }

    document.getElementById('fiscalYearModal').addEventListener('click', function (event) {
        if (event.target === this) {
            closeFiscalYearModal();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeFiscalYearModal();
        }
    });
</script>
@endsection