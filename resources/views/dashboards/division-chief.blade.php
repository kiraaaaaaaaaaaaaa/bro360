
@extends('layouts.app')

@section('title', 'Division Head Dashboard')
@section('page-title', 'Division Head Dashboard')
@section('page-description', 'Monitor division performance, PAP implementation, tasks, and current reviews.')

@section('content')

<div class="dashboard-page">

    {{-- Welcome Banner --}}
    <div class="welcome-banner">
        <div>
            <h2>Welcome to BRO360!</h2>
            <p>Here is your division's performance and implementation overview.</p>
        </div>
    </div>

{{-- DIVISION SUMMARY --}}
<h3 class="dashboard-section-title">Division Summary</h3>

<div class="stats-grid">

    <div class="stat-card">
        <div class="stat-top">
            <span class="stat-label">Total PAPs</span>
            <div class="stat-icon">▤</div>
        </div>
        <h3 class="stat-value">{{ $totalPaps }}</h3>
    </div>

    <div class="stat-card">
        <div class="stat-top">
            <span class="stat-label">Total Activities</span>
            <div class="stat-icon">▦</div>
        </div>
        <h3 class="stat-value">{{ $totalActivities }}</h3>
    </div>

    <div class="stat-card">
        <div class="stat-top">
            <span class="stat-label">Total Tasks</span>
            <div class="stat-icon">☷</div>
        </div>
        <h3 class="stat-value">{{ $totalTasks }}</h3>
    </div>

    <div class="stat-card">
        <div class="stat-top">
            <span class="stat-label">In Progress</span>
            <div class="stat-icon">◷</div>
        </div>
        <h3 class="stat-value">{{ $activeTasks }}</h3>
    </div>

    <div class="stat-card">
        <div class="stat-top">
            <span class="stat-label">Completed</span>
            <div class="stat-icon">✓</div>
        </div>
        <h3 class="stat-value">{{ $completedTasks }}</h3>
    </div>

    <div class="stat-card">
        <div class="stat-top">
            <span class="stat-label">For Revision</span>
            <div class="stat-icon">↻</div>
        </div>
        <h3 class="stat-value">{{ $revisionTasks }}</h3>
    </div>

</div>


{{-- TASK STATUS OVERVIEW --}}
<h3 class="dashboard-section-title">Task Status Overview</h3>

<div class="stats-grid">

    <div class="stat-card">
        <div class="stat-top">
            <span class="stat-label">Pending</span>
            <div class="stat-icon">◌</div>
        </div>
        <h3 class="stat-value">{{ $pendingTasks }}</h3>
    </div>

    <div class="stat-card">
        <div class="stat-top">
            <span class="stat-label">For Review</span>
            <div class="stat-icon">⌛</div>
        </div>
        <h3 class="stat-value">{{ $forReviewTasks }}</h3>
    </div>

    <div class="stat-card">
        <div class="stat-top">
            <span class="stat-label">On Hold</span>
            <div class="stat-icon">Ⅱ</div>
        </div>
        <h3 class="stat-value">{{ $onHoldTasks }}</h3>
    </div>

</div>


    {{-- Dashboard Content --}}
    <div class="dashboard-grid">

        {{-- Current Reviews --}}
        <div class="panel">

            <div class="panel-header">
                <h3>Current Reviews</h3>
                <span>{{ $currentReviews->count() }} tasks</span>
            </div>

            <div class="panel-body">

                @forelse($currentReviews as $review)

                    <a href="{{ route('tasks.show', $review['task']->id) }}"
                       class="schedule-item">

                        <div class="schedule-details">

                            <strong>{{ $review['task']->title }}</strong>

                            <div class="pending-action-meta">
                                <span>
                                    {{ $review['task']->pap?->pap_code ?? 'No PAP' }}
                                </span>

                                <span class="task-status-badge status-pending">
                                    Stage {{ $review['review_order'] }}
                                </span>
                            </div>

                            @if($review['reviewers']->isNotEmpty())
                                <small>
                                    <strong>Reviewer:</strong>
                                    {{ $review['reviewers']->pluck('name')->implode(', ') }}
                                </small>
                            @endif

                            @if($review['destinations']->isNotEmpty())
                                <small>
                                    <strong>Routing:</strong>
                                    {{ $review['destinations']->pluck('name')->implode(', ') }}
                                </small>
                            @endif

                            <small>
                                Submitted:
                                {{ $review['task']->updated_at?->format('M d, Y') ?? '—' }}
                            </small>

                        </div>

                    </a>

                @empty

                    <p class="empty-state">
                        There are no tasks currently undergoing review.
                    </p>

                @endforelse

            </div>
        </div>

        {{-- Upcoming Activities --}}
        <x-dashboard.upcoming-activities :activities="$upcomingActivities" />

    </div>

    {{-- PAP Implementation --}}
    <div class="panel">

        <div class="panel-header">
            <h3>PAP Implementation Overview</h3>
            <span>Overall: {{ number_format($overallImplementation, 0) }}%</span>
        </div>

        <div class="panel-body">

            <div class="task-overview-table-wrapper">

                <table class="task-overview-table">

                    <thead>
                        <tr>
                            <th>PAP Code</th>
                            <th>PAP Name</th>
                            <th>Activities</th>
                            <th>Progress</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($papImplementation as $pap)

                            <tr>
                                <td>{{ $pap['code'] ?? '—' }}</td>

                                <td>{{ $pap['name'] }}</td>

                                <td>{{ $pap['activity_count'] }}</td>

                                <td>
                                    <div class="task-progress-wrapper">

                                        <div class="task-progress-track">
                                            <div class="task-progress-fill"
                                                 style="width: {{ $pap['progress'] }}%;">
                                            </div>
                                        </div>

                                        <small>
                                            {{ number_format($pap['progress'], 0) }}%
                                        </small>

                                    </div>
                                </td>
                            </tr>

                        @empty

                            <tr>
                                <td colspan="4" class="empty-state">
                                    No PAPs found in your division.
                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>
    </div>

    {{-- Division Task Monitoring --}}
    <div class="panel">

        <div class="panel-header">
            <h3>Division Task Monitoring</h3>
            <span>{{ $divisionTaskList->count() }} tasks</span>
        </div>

        <div class="panel-body">

            <div class="task-overview-table-wrapper">

                <table class="task-overview-table">

                    <thead>
                        <tr>
                            <th>Task</th>
                            <th>PAP</th>
                            <th>Activity</th>
                            <th>Priority</th>
                            <th>Progress</th>
                            <th>Status</th>
                            <th>Due Date</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($divisionTaskList as $task)

                            <tr>
                                <td>
                                    <a href="{{ route('tasks.show', $task->id) }}">
                                        {{ $task->title }}
                                    </a>
                                </td>

                                <td>{{ $task->pap?->pap_code ?? '—' }}</td>

                                <td>{{ $task->activity?->title ?? '—' }}</td>

                                <td>{{ $task->priority ?? '—' }}</td>

                                <td>
                                    <div class="task-progress-wrapper">

                                        <div class="task-progress-track">
                                            <div class="task-progress-fill"
                                                 style="width: {{ $task->progress }}%;">
                                            </div>
                                        </div>

                                        <small>
                                            {{ number_format($task->progress, 0) }}%
                                        </small>

                                    </div>
                                </td>

                                <td>{{ $task->status?->name ?? 'Unknown' }}</td>

                                <td>
                                    {{ $task->due_date?->format('M d, Y') ?? '—' }}
                                </td>
                            </tr>

                        @empty

                            <tr>
                                <td colspan="7" class="empty-state">
                                    No tasks found in your division.
                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>
    </div>

    {{-- Staff Workload --}}
    <div class="panel">

        <div class="panel-header">
            <h3>Staff Workload</h3>
            <span>{{ $divisionStaff->count() }} staff</span>
        </div>

        <div class="panel-body">

            <div class="task-overview-table-wrapper">

                <table class="task-overview-table">

                    <thead>
                        <tr>
                            <th>Staff Name</th>
                            <th>Total Tasks</th>
                            <th>In Progress</th>
                            <th>Completed</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($divisionStaff as $staff)

                            <tr>
                                <td>{{ $staff['name'] }}</td>

                                <td>{{ $staff['total_tasks'] }}</td>

                                <td>{{ $staff['active_tasks'] }}</td>

                                <td>{{ $staff['completed_tasks'] }}</td>
                            </tr>

                        @empty

                            <tr>
                                <td colspan="4" class="empty-state">
                                    No staff members found in your division.
                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>
    </div>

    {{-- Recent Task Updates --}}
    <div class="panel">

        <div class="panel-header">
            <h3>Recent Task Updates</h3>
        </div>

        <div class="panel-body">

            <div class="task-overview-table-wrapper">

                <table class="task-overview-table">

                    <thead>
                        <tr>
                            <th>Task</th>
                            <th>PAP</th>
                            <th>Activity</th>
                            <th>Last Updated</th>
                        </tr>
                    </thead>

                    <tbody>

                        @forelse($recentTaskUpdates as $task)

                            <tr>
                                <td>
                                    <a href="{{ route('tasks.show', $task->id) }}">
                                        {{ $task->title }}
                                    </a>
                                </td>

                                <td>{{ $task->pap?->pap_code ?? '—' }}</td>

                                <td>{{ $task->activity?->title ?? '—' }}</td>

                                <td>
                                    {{ $task->updated_at?->format('M d, Y h:i A') ?? '—' }}
                                </td>
                            </tr>

                        @empty

                            <tr>
                                <td colspan="4" class="empty-state">
                                    No recent task updates available.
                                </td>
                            </tr>

                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>
    </div>

</div>

@endsection
