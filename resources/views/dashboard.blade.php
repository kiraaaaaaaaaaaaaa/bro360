
@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('page-description', 'Overview of BRO operations and performance')

@section('content')
<link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">

<div class="dashboard-page">

    {{-- WELCOME BANNER --}}
    <div class="welcome-banner">
        <div class="welcome-label">BANGSAMORO REVENUE OFFICE</div>
        <h2>Welcome to BRO360!</h2>
    </div>

    {{-- OFFICE OVERVIEW --}}
    <section class="dashboard-section">
        <div class="section-title">Office Overview</div>

        <div class="stats-grid">

            <div class="stat-card">
                <div class="stat-top">
                    <span class="stat-label">Total PAPs</span>
                    <div class="stat-icon">▤</div>
                </div>
                <div class="stat-value">{{ number_format($totalPaps) }}</div>
                <div class="stat-note">Registered programs and projects</div>
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <span class="stat-label">Total Activities</span>
                    <div class="stat-icon">▧</div>
                </div>
                <div class="stat-value">{{ number_format($totalActivities) }}</div>
                <div class="stat-note">Registered PAP activities</div>
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <span class="stat-label">Total Tasks</span>
                    <div class="stat-icon">☷</div>
                </div>
                <div class="stat-value">{{ number_format($totalTasks) }}</div>
                <div class="stat-note">All registered tasks</div>
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <span class="stat-label">Active Tasks</span>
                    <div class="stat-icon">◷</div>
                </div>
                <div class="stat-value">{{ number_format($activeTasks) }}</div>
                <div class="stat-note">Currently in progress</div>
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <span class="stat-label">Pending Tasks</span>
                    <div class="stat-icon">◌</div>
                </div>
                <div class="stat-value">{{ number_format($pendingTasks) }}</div>
                <div class="stat-note">Tasks not yet started</div>
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <span class="stat-label">Completed Tasks</span>
                    <div class="stat-icon">✓</div>
                </div>
                <div class="stat-value">{{ number_format($completedTasks) }}</div>
                <div class="stat-note">Successfully completed</div>
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <span class="stat-label">Pending Reviews</span>
                    <div class="stat-icon">⌛</div>
                </div>
                <div class="stat-value">{{ number_format($pendingReviews) }}</div>
                <div class="stat-note">Awaiting reviewer action</div>
            </div>

        </div>
    </section>

    {{-- MASONRY DASHBOARD --}}
    <div class="dashboard-masonry">

        {{-- LEFT COLUMN --}}
        <div class="dashboard-masonry-left">

            {{-- PAP IMPLEMENTATION OVERVIEW --}}
            <div class="panel">
                <div class="panel-header">
                    <h3>PAP Implementation Overview</h3>
                    <span class="view-link">{{ $papImplementation->count() }} PAPs</span>
                </div>

                <div class="implementation-summary">

                    <div class="circular-progress" style="--progress: {{ $overallImplementation }}%">
                        <div class="circular-progress-inner">
                            <strong>{{ number_format($overallImplementation, 1) }}%</strong>
                        </div>
                    </div>

                    <div class="implementation-summary-info">
                        <span class="implementation-label">Overall Implementation</span>
                        <p>Average implementation progress across all PAPs</p>
                    </div>

                </div>

                <div class="pap-implementation-list">
                    @forelse($papImplementation as $pap)
                        <div class="pap-implementation-item">
                            <div class="pap-implementation-info">
                                <div>
                                    <strong>{{ $pap['name'] }}</strong>
                                    <small>
                                        {{ $pap['code'] ?? 'No PAP code' }}
                                        · {{ $pap['activity_count'] }} activities
                                    </small>
                                </div>

                                <span class="pap-progress-value">
                                    {{ number_format($pap['progress'], 1) }}%
                                </span>
                            </div>

                            <div class="pap-progress-track">
                                <div class="pap-progress-fill" style="width: {{ $pap['progress'] }}%"></div>
                            </div>
                        </div>
                    @empty
                        <div class="empty-state">
                            <strong>No PAP records found</strong>
                            <p>Registered PAP implementation progress will appear here.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- TASK OVERVIEW --}}
            <div class="panel task-overview-panel">

                <div class="panel-header">
                    <div>
                        <h3>Task Overview</h3>
                        <p class="panel-description">
                            Status and current reviewer or routing of required documents per activity.
                        </p>
                    </div>

                    <a href="{{ route('tasks.index') }}" class="view-link">
                        View all tasks
                    </a>
                </div>

                <div class="task-overview-table-wrapper">
                    <table class="task-overview-table">
                        <thead>
                            <tr>
                                <th>PAP Activity</th>
                                <th>Concept Note</th>
                                <th>APF</th>
                                <th>Memo</th>
                                <th>PAR</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($taskOverview as $activity)
                                <tr>
                                    <td>
                                        <strong>{{ $activity['activity_title'] }}</strong>
                                        <small>{{ $activity['pap_name'] }}</small>
                                    </td>

                                    @foreach(['concept_note', 'apf', 'memo', 'par'] as $category)
                                        <td>
                                            @if($activity['categories'][$category])
                                                <a href="{{ route('tasks.show', $activity['categories'][$category]['id']) }}"
                                                   class="task-status-link"
                                                   title="{{ $activity['categories'][$category]['title'] }}">
                                                    {{ $activity['categories'][$category]['status'] }}
                                                </a>

                                                <div class="task-current-stage">
                                                    {{ $activity['categories'][$category]['current_stage'] }}
                                                </div>
                                            @else
                                                <span class="task-not-available">—</span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="task-table-empty">
                                        No PAP activities found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>

        </div>

        {{-- RIGHT COLUMN --}}
        <div class="dashboard-masonry-right">

            {{-- UPCOMING SCHEDULE --}}
            <div class="panel">
                <div class="panel-header">
                    <h3>Upcoming Schedule</h3>
                    <a href="{{ route('calendar.index') }}" class="view-link">
                        View Calendar
                    </a>
                </div>

                <div class="schedule-list">

                    {{-- PAP ACTIVITIES --}}
                    <div class="schedule-group">
                        <h4>PAP Activities</h4>

                        @forelse($upcomingActivities as $activity)
                            <a href="{{ route('activities.show', $activity) }}" class="schedule-item">

                                <div class="schedule-date activity-date">
                                    <span>{{ $activity->start_date->format('d') }}</span>
                                    <small>{{ $activity->start_date->format('M') }}</small>
                                </div>

                                <div class="schedule-details">
                                    <strong>{{ $activity->title }}</strong>
                                    <small>{{ $activity->pap->name ?? 'No PAP assigned' }}</small>
                                </div>

                                <span class="schedule-type activity-type">Activity</span>
                            </a>
                        @empty
                            <p class="schedule-empty">No upcoming PAP activities.</p>
                        @endforelse
                    </div>

                    {{-- UPCOMING TASKS --}}
                    <div class="schedule-group">
                        <h4>Upcoming Tasks</h4>

                        @forelse($upcomingTasks as $task)
                            @php
                                $taskDate = $task->due_date ?? $task->start_date;
                            @endphp

                            <a href="{{ route('tasks.show', $task) }}" class="schedule-item">

                                <div class="schedule-date task-date">
                                    <span>{{ $taskDate->format('d') }}</span>
                                    <small>{{ $taskDate->format('M') }}</small>
                                </div>

                                <div class="schedule-details">
                                    <strong>{{ $task->title }}</strong>
                                    <small>{{ $task->pap->name ?? 'No PAP assigned' }}</small>
                                </div>

                                <span class="schedule-type task-type">Task</span>
                            </a>
                        @empty
                            <p class="schedule-empty">No upcoming tasks.</p>
                        @endforelse
                    </div>

                </div>
            </div>

        </div>

    </div>

</div>

@endsection
