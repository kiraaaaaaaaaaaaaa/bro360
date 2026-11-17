@extends('layouts.app')

@section('title', 'Fund Source Management')

@section('content')
<div class="fund-source-page">

    {{-- PAGE HEADER --}}
    <div class="fund-source-header">
        <div>
            <div class="fund-source-breadcrumb">
                Budget Management <i class="fa-solid fa-chevron-right"></i> Fund Sources
            </div>
            <h1>Fund Source Management</h1>
            <p>Manage and maintain the funding sources available for BRO programs and activities.</p>
        </div>

        @can('fund-sources.create')
        <button type="button" class="fs-btn fs-btn-primary" onclick="openFundSourceModal()">
            <i class="fa-solid fa-plus"></i>
            Add Fund Source
        </button>
        @endcan
    </div>

    {{-- FLASH MESSAGES --}}
    @if(session('success'))
    <div class="fs-alert fs-alert-success">
        <i class="fa-solid fa-circle-check"></i>
        {{ session('success') }}
        <button type="button" onclick="this.parentElement.remove()">
            <i class="fa-solid fa-xmark"></i>
        </button>
    </div>
    @endif

    @if(session('error'))
    <div class="fs-alert fs-alert-error">
        <i class="fa-solid fa-circle-exclamation"></i>
        {{ session('error') }}
    </div>
    @endif

    @if($errors->any())
    <div class="fs-alert fs-alert-error">
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
    <div class="fs-summary-grid">

        <div class="fs-summary-card">
            <div class="fs-summary-icon fs-icon-total">
                <i class="fa-solid fa-wallet"></i>
            </div>
            <div>
                <span>Total Fund Sources</span>
                <h2>{{ number_format($counts['total']) }}</h2>
                <small>All registered sources</small>
            </div>
        </div>

        <div class="fs-summary-card">
            <div class="fs-summary-icon fs-icon-active">
                <i class="fa-solid fa-circle-check"></i>
            </div>
            <div>
                <span>Active Fund Sources</span>
                <h2>{{ number_format($counts['active']) }}</h2>
                <small>Available for selection</small>
            </div>
        </div>

        <div class="fs-summary-card">
            <div class="fs-summary-icon fs-icon-inactive">
                <i class="fa-solid fa-circle-pause"></i>
            </div>
            <div>
                <span>Inactive Fund Sources</span>
                <h2>{{ number_format($counts['inactive']) }}</h2>
                <small>Currently unavailable</small>
            </div>
        </div>

    </div>

    {{-- MAIN CONTENT --}}
    <div class="fs-panel">

        <div class="fs-panel-header">
            <div>
                <h3>Registered Fund Sources</h3>
                <p>View, search, and manage your funding source records.</p>
            </div>

            <span class="fs-record-count">
                {{ $fundSources->total() }} records
            </span>
        </div>

        {{-- FILTERS --}}
        <form method="GET" action="{{ route('fund-sources.index') }}" class="fs-filter-form">

            <div class="fs-search-box">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, code, or description...">
            </div>

            <select name="status" class="fs-filter-select">
                <option value="">All Status</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>
                    Active
                </option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>
                    Inactive
                </option>
            </select>

            <button type="submit" class="fs-btn fs-btn-filter">
                <i class="fa-solid fa-filter"></i>
                Filter
            </button>

            @if(request()->hasAny(['search', 'status']))
            <a href="{{ route('fund-sources.index') }}" class="fs-btn fs-btn-reset">
                Reset
            </a>
            @endif
        </form>

        {{-- TABLE --}}
        <div class="fs-table-wrapper">
            <table class="fs-table">
                <thead>
                    <tr>
                        <th style="width: 65px;">#</th>
                        <th>Fund Source</th>
                        <th>Code</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th class="fs-actions-heading">Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($fundSources as $index => $fundSource)
                    <tr>
                        <td class="fs-row-number">
                            {{ $fundSources->firstItem() + $index }}
                        </td>

                        <td>
                            <div class="fs-name-cell">
                                <div class="fs-name-icon">
                                    <i class="fa-solid fa-building-columns"></i>
                                </div>
                                <div>
                                    <strong>{{ $fundSource->name }}</strong>
                                    <small>Fund Source</small>
                                </div>
                            </div>
                        </td>

                        <td>
                            @if($fundSource->code)
                            <span class="fs-code">
                                {{ $fundSource->code }}
                            </span>
                            @else
                            <span class="fs-empty">Not specified</span>
                            @endif
                        </td>

                        <td>
                            <span class="fs-description" title="{{ $fundSource->description }}">
                                {{ $fundSource->description ?: 'No description provided' }}
                            </span>
                        </td>

                        <td>
                            @if($fundSource->is_active)
                            <span class="fs-status fs-status-active">
                                <span></span> Active
                            </span>
                            @else
                            <span class="fs-status fs-status-inactive">
                                <span></span> Inactive
                            </span>
                            @endif
                        </td>

                        <td>
                            <div class="fs-action-buttons">

                                @can('fund-sources.update')
                                <button type="button" class="fs-action-btn fs-edit-btn" title="Edit" onclick="editFundSource(
                                                    {{ $fundSource->id }},
                                                    @js($fundSource->code),
                                                    @js($fundSource->name),
                                                    @js($fundSource->description)
                                                )">
                                    Edit
                                </button>

                                <form method="POST" action="{{ route('fund-sources.toggle-status', $fundSource) }}" onsubmit="return confirm('Are you sure you want to {{ $fundSource->is_active ? 'deactivate' : 'activate' }} this fund source?');">
                                    @csrf

                                    <button type="submit" class="fs-action-btn {{ $fundSource->is_active ? 'fs-deactivate-btn' : 'fs-activate-btn' }}">
                                        {{ $fundSource->is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>
                                @endcan

                                @if(!auth()->user()->can('fund-sources.update'))
                                <span class="fs-empty">No actions</span>
                                @endif

                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6">
                            <div class="fs-empty-state">
                                <div>
                                    <i class="fa-solid fa-wallet"></i>
                                </div>
                                <h3>No fund sources found</h3>
                                <p>
                                    @if(request()->hasAny(['search', 'status']))
                                    Try changing your search or filters.
                                    @else
                                    Start by adding your first fund source.
                                    @endif
                                </p>

                                @can('fund-sources.create')
                                <button type="button" class="fs-btn fs-btn-primary" onclick="openFundSourceModal()">
                                    <i class="fa-solid fa-plus"></i>
                                    Add Fund Source
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
        @if($fundSources->hasPages())
        <div class="fs-pagination">
            <div class="fs-pagination-info">
                Showing
                <strong>{{ $fundSources->firstItem() }}</strong>
                to
                <strong>{{ $fundSources->lastItem() }}</strong>
                of
                <strong>{{ $fundSources->total() }}</strong>
                records
            </div>

            {{ $fundSources->links() }}
        </div>
        @endif

    </div>
</div>

{{-- ADD / EDIT MODAL --}}
<div class="fs-modal-overlay" id="fundSourceModal" hidden>
    <div class="fs-modal">

        <div class="fs-modal-header">
            <div>
                <span class="fs-modal-eyebrow">BUDGET MANAGEMENT</span>
                <h2 id="fundSourceModalTitle">Add Fund Source</h2>
                <p id="fundSourceModalSubtitle">
                    Register a new funding source.
                </p>
            </div>

            <button type="button" class="fs-modal-close" onclick="closeFundSourceModal()">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form id="fundSourceForm" method="POST" action="{{ route('fund-sources.store') }}">
            @csrf

            <div id="fundSourceMethod"></div>

            <div class="fs-modal-body">

                <div class="fs-form-field">
                    <label for="fs_code">
                        Fund Source Code
                        <span class="fs-optional">Optional</span>
                    </label>

                    <input type="text" id="fs_code" name="code" maxlength="60" placeholder="e.g. GAA-2026">

                    <small>A unique identifier for this funding source.</small>
                </div>

                <div class="fs-form-field">
                    <label for="fs_name">
                        Fund Source Name <span class="fs-required">*</span>
                    </label>

                    <input type="text" id="fs_name" name="name" maxlength="180" placeholder="Enter fund source name" required>
                </div>

                <div class="fs-form-field">
                    <label for="fs_description">
                        Description
                        <span class="fs-optional">Optional</span>
                    </label>

                    <textarea id="fs_description" name="description" rows="4" placeholder="Provide additional details about this fund source..."></textarea>
                </div>

                <div class="fs-form-note">
                    <i class="fa-solid fa-circle-info"></i>
                    <span>
                        Newly registered fund sources will be active by default
                        and available for selection in applicable modules.
                    </span>
                </div>

            </div>

            <div class="fs-modal-footer">
                <button type="button" class="fs-btn fs-btn-reset" onclick="closeFundSourceModal()">
                    Cancel
                </button>

                <button type="submit" class="fs-btn fs-btn-primary" id="fundSourceSubmitBtn">
                    <i class="fa-solid fa-floppy-disk"></i>
                    Save Fund Source
                </button>
            </div>
        </form>

    </div>
</div>

@endsection

@push('scripts')
<script>
    const fundSourceBaseUrl = @json(url('/fund-sources'));

    function openFundSourceModal() {
        const modal = document.getElementById('fundSourceModal');
        const form = document.getElementById('fundSourceForm');

        form.reset();
        form.action = @json(route('fund-sources.store'));

        document.getElementById('fundSourceMethod').innerHTML = '';

        document.getElementById('fundSourceModalTitle').textContent = 'Add Fund Source';
        document.getElementById('fundSourceModalSubtitle').textContent =
            'Register a new funding source.';

        document.getElementById('fundSourceSubmitBtn').innerHTML =
            '<i class="fa-solid fa-floppy-disk"></i> Save Fund Source';

        modal.hidden = false;
        modal.classList.add('show');
        document.body.classList.add('fs-modal-open');

        setTimeout(() => document.getElementById('fs_name').focus(), 100);
    }

    function editFundSource(id, code, name, description) {
        const modal = document.getElementById('fundSourceModal');
        const form = document.getElementById('fundSourceForm');

        form.reset();
        form.action = `${fundSourceBaseUrl}/${id}`;

        document.getElementById('fundSourceMethod').innerHTML =
            '<input type="hidden" name="_method" value="PUT">';

        document.getElementById('fs_code').value = code ? ? '';
        document.getElementById('fs_name').value = name ? ? '';
        document.getElementById('fs_description').value = description ? ? '';

        document.getElementById('fundSourceModalTitle').textContent = 'Edit Fund Source';
        document.getElementById('fundSourceModalSubtitle').textContent =
            'Update the details of this funding source.';

        document.getElementById('fundSourceSubmitBtn').innerHTML =
            '<i class="fa-solid fa-floppy-disk"></i> Update Fund Source';

        modal.hidden = false;
        modal.classList.add('show');
        document.body.classList.add('fs-modal-open');

        setTimeout(() => document.getElementById('fs_name').focus(), 100);
    }

    function closeFundSourceModal() {
        const modal = document.getElementById('fundSourceModal');

        modal.classList.remove('show');
        modal.hidden = true;

        document.body.classList.remove('fs-modal-open');
    }

    document.getElementById('fundSourceModal').addEventListener('click', function(event) {
        if (event.target === this) {
            closeFundSourceModal();
        }
    });

    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeFundSourceModal();
        }
    });

    // Reopen the modal if validation fails.
    @if($errors -> any())
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('fundSourceForm');

        @if(old('_method') === 'PUT')
        const fundSourceId = @json(request()->route('fundSource')?->id);
        if (fundSourceId) {
            form.action = `${fundSourceBaseUrl}/${fundSourceId}`;
            document.getElementById('fundSourceMethod').innerHTML =
                '<input type="hidden" name="_method" value="PUT">';
            document.getElementById('fundSourceModalTitle').textContent = 'Edit Fund Source';
            document.getElementById('fundSourceSubmitBtn').innerHTML =
                '<i class="fa-solid fa-floppy-disk"></i> Update Fund Source';
        }
        @endif

        document.getElementById('fundSourceModal').hidden = false;
        document.getElementById('fundSourceModal').classList.add('show');
        document.body.classList.add('fs-modal-open');
    });
    @endif

</script>
@endpush
