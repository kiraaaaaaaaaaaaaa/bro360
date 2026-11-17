
@extends('layouts.app')

@section('content')

<div class="page-container activity-index-page">

    {{-- PAGE HEADER --}}
    <div class="activity-index-header">
        <div>
            <div class="activity-eyebrow">PROGRAM IMPLEMENTATION</div>
            <h1>PAP Activities</h1>
            <p>Manage scheduled activities and monitor their implementation progress.</p>
        </div>

        <a href="{{ route('activities.create') }}" class="activity-create-btn">
            + Create Activity
        </a>
    </div>

    {{-- SUCCESS MESSAGE --}}
    @if(session('success'))
        <div class="activity-success-message">
            {{ session('success') }}
        </div>
    @endif

    {{-- SUMMARY --}}
    <div class="activity-summary-grid">

        <div class="activity-summary-card">
            <div class="activity-summary-label">TOTAL ACTIVITIES</div>

            <div class="activity-summary-value">
                {{ $activities->total() }}
            </div>

            <div class="activity-summary-description">
                Registered PAP activities
            </div>
        </div>

    </div>

    {{-- ACTIVITY RECORDS --}}
    <section class="activity-records-section">

        <div class="activity-records-header">
            <div>
                <h2>Activity Records</h2>
                <p>Overview of scheduled activities and their current status.</p>
            </div>

            <span class="activity-record-count">
                {{ $activities->total() }} Records
            </span>
        </div>

        <div class="activity-table-wrapper">

            <table class="activity-index-table">

                <thead>
                    <tr>
                        <th class="activity-column">ACTIVITY</th>
                        <th>PARENT PAP</th>
                        <th>SCHEDULE</th>
                        <th>FOCAL PERSON</th>
                        <th class="text-center">TASKS</th>
                        <th>IMPLEMENTATION</th>
                        <th>STATUS</th>
                        <th class="text-right">ACTION</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($activities as $activity)

                        <tr>

                            {{-- ACTIVITY --}}
                            <td class="activity-main-cell">

                                <a href="{{ route('activities.show', $activity) }}"
                                   class="activity-title-link">
                                    {{ $activity->title }}
                                </a>

                                @if($activity->location)
                                    <span class="activity-location">
                                        {{ $activity->location }}
                                    </span>
                                @endif

                            </td>

                            {{-- PARENT PAP --}}
                            <td>

                                @if($activity->pap)

                                    <div class="activity-pap-code">
                                        {{ $activity->pap->pap_code }}
                                    </div>

                                    <div class="activity-pap-name">
                                        {{ $activity->pap->name }}
                                    </div>

                                @else
                                    <span class="activity-muted">Unavailable</span>
                                @endif

                            </td>

                            {{-- SCHEDULE --}}
                            <td>

                                @if($activity->start_date || $activity->end_date)

                                    <div class="activity-schedule-date">
                                        {{ $activity->start_date?->format('M d, Y') ?? 'Not set' }}
                                    </div>

                                    <div class="activity-schedule-separator">
                                        to
                                    </div>

                                    <div class="activity-schedule-date">
                                        {{ $activity->end_date?->format('M d, Y') ?? 'Not set' }}
                                    </div>

                                @else
                                    <span class="activity-muted">Not scheduled</span>
                                @endif

                                @if($activity->quarter)
                                    <span class="activity-quarter">
                                        {{ $activity->quarter }}
                                    </span>
                                @endif

                            </td>

                            {{-- FOCAL PERSON --}}
                            <td>
                                <span class="activity-focal-person">
                                    {{ $activity->focalPerson?->name ?? 'Not assigned' }}
                                </span>
                            </td>

                            {{-- TASK COUNT --}}
                            <td class="text-center">
                                <span class="activity-task-count">
                                    {{ $activity->tasks_count }}
                                </span>
                            </td>

                            {{-- PROGRESS --}}
                            <td>

                                <div class="activity-progress-heading">
                                    <strong>{{ number_format($activity->progress, 0) }}%</strong>
                                    <span>Complete</span>
                                </div>

                                <div class="activity-progress-track">
                                    <div
                                        class="activity-progress-value"
                                        style="width: {{ min(100, max(0, $activity->progress)) }}%;">
                                    </div>
                                </div>

                            </td>

                            {{-- STATUS --}}
                            <td>
                                <span class="activity-status status-{{ strtolower($activity->status) }}">
                                    {{ $activity->status }}
                                </span>
                            </td>

                            {{-- ACTION --}}
                            <td class="activity-action-cell">

                                <a href="{{ route('activities.show', $activity) }}"
                                   class="activity-view-action">
                                    View
                                </a>

                                <a href="{{ route('activities.edit', $activity) }}"
                                   class="activity-edit-action">
                                    Edit
                                </a>

                            </td>

                        </tr>

                    @empty

                        <tr>
                            <td colspan="8">

                                <div class="activity-empty-state">
                                    <h3>No activities found</h3>

                                    <p>
                                        There are currently no activities registered in the system.
                                    </p>

                                    <a href="{{ route('activities.create') }}"
                                       class="activity-create-btn">
                                        Create First Activity
                                    </a>
                                </div>

                            </td>
                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

        {{-- PAGINATION --}}
        @if($activities->hasPages())

            <div class="activity-pagination">
                {{ $activities->links() }}
            </div>

        @endif

    </section>

</div>

@endsection
