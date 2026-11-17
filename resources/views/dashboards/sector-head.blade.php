
@extends('layouts.app')

@section('title', 'Sector Head Dashboard')
@section('page-title', 'Dashboard')
@section('page-description', 'Overview of BRO operations and performance across all divisions')

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
                
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <span class="stat-label">Total Activities</span>
                    <div class="stat-icon">▧</div>
                </div>
                <div class="stat-value">{{ number_format($totalActivities) }}</div>
                
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <span class="stat-label">Total Tasks</span>
                    <div class="stat-icon">☷</div>
                </div>
                <div class="stat-value">{{ number_format($totalTasks) }}</div>
                
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <span class="stat-label">Active Tasks</span>
                    <div class="stat-icon">◷</div>
                </div>
                <div class="stat-value">{{ number_format($activeTasks) }}</div>
                
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <span class="stat-label">Pending Tasks</span>
                    <div class="stat-icon">◌</div>
                </div>
                <div class="stat-value">{{ number_format($pendingTasks) }}</div>
                
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <span class="stat-label">Completed Tasks</span>
                    <div class="stat-icon">✓</div>
                </div>
                <div class="stat-value">{{ number_format($completedTasks) }}</div>
                
            </div>

            <div class="stat-card">
                <div class="stat-top">
                    <span class="stat-label">Pending Reviews</span>
                    <div class="stat-icon">⌛</div>
                </div>
                <div class="stat-value">{{ number_format($pendingReviews) }}</div>
                
            </div>

        </div>
    </section>


    {{-- MAIN TWO-COLUMN DASHBOARD --}}
    <div class="dashboard-masonry">

        {{-- ========================================= --}}
        {{-- LEFT COLUMN --}}
        {{-- ========================================= --}}
        <div class="dashboard-masonry-left">

            {{-- PAP IMPLEMENTATION OVERVIEW --}}
            <div class="panel">

                <div class="panel-header">
                    <h3>PAP Implementation Overview</h3>
                    <span class="view-link">{{ $papImplementation->count() }} PAPs</span>
                </div>

                <div class="implementation-summary">

                    <div class="circular-progress"
                         style="--progress: {{ $overallImplementation }}%">
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
                                <div class="pap-progress-fill"
                                     style="width: {{ max(0, min(100, $pap['progress'])) }}%">
                                </div>
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
                        <h3>Activity Overview</h3>
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


            {{-- DIVISION PERFORMANCE --}}
            <div class="panel">

                <div class="panel-header">
                    <div>
                        <h3>Division Performance</h3>
                        <p class="panel-description">
                            PAP implementation and task completion across all divisions.
                        </p>
                    </div>

                    <span class="view-link">
                        {{ $divisionPerformance->count() }} Divisions
                    </span>
                </div>

                <div class="task-overview-table-wrapper">
                    <table class="task-overview-table">

                        <thead>
                            <tr>
                                <th>Division</th>
                                <th>PAPs</th>
                                <th>Implementation</th>
                                <th>Total Tasks</th>
                                <th>Completed</th>
                                <th>Active</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($divisionPerformance as $division)
                                <tr>
                                    <td>
                                        <strong>{{ $division['name'] }}</strong>
                                    </td>

                                    <td>{{ number_format($division['total_paps']) }}</td>

                                    <td>
                                        <div class="division-progress">

                                            <div class="division-progress-info">
                                                {{ number_format($division['implementation'], 1) }}%
                                            </div>

                                            <div class="task-progress-track">
                                                <div class="task-progress-fill"
                                                     style="width: {{ max(0, min(100, $division['implementation'])) }}%">
                                                </div>
                                            </div>

                                        </div>
                                    </td>

                                    <td>{{ number_format($division['total_tasks']) }}</td>
                                    <td>{{ number_format($division['completed_tasks']) }}</td>
                                    <td>{{ number_format($division['active_tasks']) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="task-table-empty">
                                        No division records found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>

                    </table>
                </div>

            </div>

        </div>


        {{-- ========================================= --}}
        {{-- RIGHT COLUMN --}}
        {{-- ========================================= --}}
        <div class="dashboard-masonry-right">

            {{-- UPCOMING SCHEDULE --}}
            <x-dashboard.upcoming-activities :activities="$upcomingActivities" />


            {{-- CURRENT REVIEWS --}}
            <div class="panel current-reviews-panel">

                <div class="panel-header">
                    <div>
                        <h3>Current Reviews</h3>
                        <p class="panel-description">
                            Tasks currently assigned to you for review.
                        </p>
                    </div>

                    <span class="view-link">
                        {{ $currentReviews->count() }} Active
                    </span>
                </div>

                <div class="panel-body">
                    <div class="schedule-list">

                        @forelse($currentReviews as $review)
                            <div class="schedule-item">

                                <div class="schedule-details">
                                    <strong>{{ $review->task->title }}</strong>

                                    <small>
                                        {{ $review->task->pap->name ?? 'No PAP assigned' }}
                                    </small>

                                    <small>
                                        Current Stage: Your Review
                                    </small>
                                </div>

                                <a href="{{ route('tasks.show', $review->task->id) }}"
                                   class="view-link">
                                    View
                                </a>

                            </div>
                        @empty
                            <div class="empty-state">
                                <strong>No active reviews</strong>
                                <p>Tasks assigned to you for review will appear here.</p>
                            </div>
                        @endforelse

                    </div>
                </div>

            </div>


            {{-- STAFF WORKLOAD --}}
            <div class="panel">

                <div class="panel-header">
                    <div>
                        <h3>Staff Workload</h3>
                        <p class="panel-description">
                            Task assignments and completion across all divisions.
                        </p>
                    </div>

                    <span class="view-link">
                        {{ $staffWorkload->count() }} Staff
                    </span>
                </div>

                <div class="task-overview-table-wrapper">
                    <table class="task-overview-table">

                        <thead>
                            <tr>
                                <th>Staff Name</th>
                                <th>Division</th>
                                <th>Total Tasks</th>
                                <th>Active</th>
                                <th>Completed</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($staffWorkload as $staff)
                                <tr>
                                    <td>
                                        <strong>{{ $staff['name'] }}</strong>
                                    </td>

                                    <td>{{ $staff['division'] }}</td>
                                    <td>{{ number_format($staff['total_tasks']) }}</td>
                                    <td>{{ number_format($staff['active_tasks']) }}</td>
                                    <td>{{ number_format($staff['completed_tasks']) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="task-table-empty">
                                        No staff records found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>

                    </table>
                </div>

            </div>

        </div>

    </div>

</div>

@endsection
