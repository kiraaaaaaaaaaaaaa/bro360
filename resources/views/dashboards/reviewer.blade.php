
@extends('layouts.app')

@section('title', 'Staff & Reviewer Dashboard')
@section('page-title', 'Staff & Reviewer Dashboard')
@section('page-description', 'Monitor your assigned tasks, review responsibilities, deadlines, and activities.')

@section('content')

<div class="dashboard-page">

    {{-- Welcome Banner --}}
    <div class="welcome-banner">
        <div>
            <h2>Welcome to BRO360!</h2>
            <p>Here is your personal task and review overview.</p>
        </div>
    </div>

    
{{-- MY TASK SUMMARY --}}
<h3 class="dashboard-section-title">My Task Summary</h3>

<div class="stats-grid">

    <div class="stat-card">
        <div class="stat-top">
            <span class="stat-label">Pending Tasks</span>
            <div class="stat-icon">◷</div>
        </div>
        <h3 class="stat-value">{{ $pendingTasks }}</h3>
    </div>

    <div class="stat-card">
        <div class="stat-top">
            <span class="stat-label">In Progress</span>
            <div class="stat-icon">↻</div>
        </div>
        <h3 class="stat-value">{{ $activeTasks }}</h3>
    </div>

    <div class="stat-card">
        <div class="stat-top">
            <span class="stat-label">For Review</span>
            <div class="stat-icon">⌕</div>
        </div>
        <h3 class="stat-value">{{ $forReviewTasks }}</h3>
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
            <div class="stat-icon">⟳</div>
        </div>
        <h3 class="stat-value">{{ $revisionTasks }}</h3>
    </div>

    <div class="stat-card">
        <div class="stat-top">
            <span class="stat-label">On Hold</span>
            <div class="stat-icon">Ⅱ</div>
        </div>
        <h3 class="stat-value">{{ $onHoldTasks }}</h3>
    </div>

</div>


{{-- MY REVIEW SUMMARY --}}
<h3 class="dashboard-section-title">My Review Summary</h3>

<div class="stats-grid">

    <div class="stat-card">
        <div class="stat-top">
            <span class="stat-label">Pending Reviews</span>
            <div class="stat-icon">◷</div>
        </div>
        <h3 class="stat-value">{{ $pendingReviews }}</h3>
    </div>

    <div class="stat-card">
        <div class="stat-top">
            <span class="stat-label">Received Reviews</span>
            <div class="stat-icon">↙</div>
        </div>
        <h3 class="stat-value">{{ $receivedReviews }}</h3>
    </div>

    <div class="stat-card">
        <div class="stat-top">
            <span class="stat-label">Completed Reviews</span>
            <div class="stat-icon">✓</div>
        </div>
        <h3 class="stat-value">{{ $completedReviews }}</h3>
    </div>

    <div class="stat-card">
        <div class="stat-top">
            <span class="stat-label">Returned Reviews</span>
            <div class="stat-icon">↩</div>
        </div>
        <h3 class="stat-value">{{ $returnedReviews }}</h3>
    </div>

</div>


    {{-- Dashboard Content --}}
    <div class="dashboard-grid">

        {{-- My Pending Reviews --}}
        <div class="panel">
            <div class="panel-header">
                <h3>My Pending Reviews</h3>
                <span>{{ $myPendingReviews->count() }} tasks</span>
            </div>

            <div class="panel-body">

                @forelse($myPendingReviews as $review)

                    @php
                        $task = $review->task;
                    @endphp

                    @if($task)
                        <a href="{{ route('tasks.show', $task->id) }}"
                           class="schedule-item">

                            <div class="schedule-details">
                                <strong>{{ $task->title }}</strong>

                                <div class="pending-action-meta">
                                    <span>{{ $task->pap?->pap_code ?? 'No PAP' }}</span>

                                    <span class="task-status-badge status-pending">
                                        {{ $review->status }}
                                    </span>
                                </div>

                                <small>
                                    Due:
                                    {{ $task->due_date?->format('M d, Y') ?? 'No deadline' }}
                                </small>
                            </div>

                        </a>
                    @endif

                @empty
                    <p class="empty-state">
                        You have no pending reviews at the moment.
                    </p>
                @endforelse

            </div>
        </div>

        {{-- Upcoming Activities --}}
        <x-dashboard.upcoming-activities :activities="$upcomingActivities" />

    </div>

    {{-- My Assigned Tasks --}}
    <div class="panel">
        <div class="panel-header">
            <h3>My Assigned Tasks</h3>
            <span>{{ $myTaskList->count() }} tasks</span>
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
                        @forelse($myTaskList as $task)

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
                                    No tasks have been assigned to you.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                </table>
            </div>

        </div>
    </div>

    {{-- My Review History --}}
    <div class="panel">
        <div class="panel-header">
            <h3>My Review History</h3>
            <span>{{ $reviewHistory->count() }} reviews</span>
        </div>

        <div class="panel-body">

            <div class="task-overview-table-wrapper">
                <table class="task-overview-table">

                    <thead>
                        <tr>
                            <th>Task</th>
                            <th>PAP</th>
                            <th>Review Status</th>
                            <th>Last Updated</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($reviewHistory as $review)

                            <tr>
                                <td>
                                    @if($review->task)
                                        <a href="{{ route('tasks.show', $review->task->id) }}">
                                            {{ $review->task->title }}
                                        </a>
                                    @else
                                        Task unavailable
                                    @endif
                                </td>

                                <td>
                                    {{ $review->task?->pap?->pap_code ?? '—' }}
                                </td>

                                <td>
                                    <span class="task-status-badge
                                        {{ $review->status === 'Returned' ? 'status-revision' : 'status-pending' }}">
                                        {{ $review->status }}
                                    </span>
                                </td>

                                <td>
                                    {{ $review->updated_at?->format('M d, Y h:i A') ?? '—' }}
                                </td>
                            </tr>

                        @empty
                            <tr>
                                <td colspan="4" class="empty-state">
                                    No review history available.
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
