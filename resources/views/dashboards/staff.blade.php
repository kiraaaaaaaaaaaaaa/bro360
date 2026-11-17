@extends('layouts.app')

@section('title', 'Staff Dashboard')
@section('page-title', 'Staff Dashboard')
@section('page-description', 'Monitor your assigned tasks, deadlines, and activities.')

@section('content')

<div class="dashboard-page">

    {{-- Welcome Banner --}}
    <div class="welcome-banner">
        <div>
            <h2>Welcome to BRO360!</h2>
            <p>Here is your personal work overview.</p>
        </div>
    </div>


    {{-- TASK SUMMARY --}}
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


    {{-- Dashboard Content --}}
    <div class="dashboard-grid">

        {{-- Upcoming Deadlines --}}

        {{-- UPCOMING DEADLINES --}}
        
{{-- UPCOMING DEADLINES --}}
<div class="panel staff-table-panel">

    <div class="panel-header">
        <div>
            <h3>Upcoming Deadlines</h3>
            <p class="panel-description">Tasks approaching their deadlines.</p>
        </div>
        <span class="panel-count">{{ $upcomingTasks->count() }} Tasks</span>
    </div>

    <div class="panel-body">
        <div class="task-overview-table-wrapper">
            <table class="task-overview-table staff-action-table">
                <thead>
                    <tr>
                        <th>Task</th>
                        <th>PAP</th>
                        <th>Status</th>
                        <th>Due Date</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($upcomingTasks as $task)
                        <tr>
                            <td>
                                <a href="{{ route('tasks.show', $task->id) }}">
                                    {{ $task->title }}
                                </a>
                            </td>

                            <td>{{ $task->pap?->pap_code ?? '—' }}</td>

                            <td>
                                <span class="task-status-badge
                                    {{ $task->status?->name === 'For Revision'
                                        ? 'status-revision' : 'status-pending' }}">
                                    {{ $task->status?->name ?? 'Unknown' }}
                                </span>
                            </td>

                            <td>
                                {{ $task->due_date?->format('M d, Y') ?? 'No deadline' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="empty-state">
                                You have no upcoming deadlines.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>




        {{-- Upcoming Activities --}}
        <x-dashboard.upcoming-activities :activities="$upcomingActivities" />

    </div>


    {{-- My Pending Actions --}}

    {{-- MY PENDING ACTIONS --}}
    
{{-- MY PENDING ACTIONS --}}
<div class="panel staff-table-panel">

    <div class="panel-header">
        <div>
            <h3>My Pending Actions</h3>
            <p class="panel-description">Tasks that require your attention.</p>
        </div>
        <span class="panel-count">{{ $pendingActionTasks->count() }} Tasks</span>
    </div>

    <div class="panel-body">
        <div class="task-overview-table-wrapper">
            <table class="task-overview-table staff-action-table">
                <thead>
                    <tr>
                        <th>Task</th>
                        <th>PAP</th>
                        <th>Status</th>
                        <th>Due Date</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($pendingActionTasks as $task)
                        <tr>
                            <td>
                                <a href="{{ route('tasks.show', $task->id) }}">
                                    {{ $task->title }}
                                </a>
                            </td>

                            <td>{{ $task->pap?->pap_code ?? '—' }}</td>

                            <td>
                                <span class="task-status-badge
                                    {{ $task->status?->name === 'For Revision'
                                        ? 'status-revision' : 'status-pending' }}">
                                    {{ $task->status?->name ?? 'Unknown' }}
                                </span>
                            </td>

                            <td>
                                {{ $task->due_date?->format('M d, Y') ?? 'No deadline' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="empty-state">
                                You have no pending actions at the moment.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>




    {{-- My Assigned Tasks --}}
    <div class="panel">
        <div class="panel-header">
            <h3>My Assigned Tasks</h3>
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
                                        <div class="task-progress-fill" style="width: {{ $task->progress }}%;">
                                        </div>
                                    </div>

                                    <small>{{ number_format($task->progress, 0) }}%</small>
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

</div>

@endsection
