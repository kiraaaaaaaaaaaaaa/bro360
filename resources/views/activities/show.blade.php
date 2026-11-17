@extends('layouts.app')

@section('content')

@php
$progress = min(100, max(0, $activity->progress));
$taskCount = $activity->tasks->count();
@endphp

<div class="page-container activity-show-page">

    {{-- PAGE HEADER --}}
    <div class="activity-show-header">

        <div class="activity-show-heading">
            <div class="activity-show-eyebrow">PROGRAM IMPLEMENTATION / ACTIVITY DETAILS</div>

            <h1>{{ $activity->title }}</h1>

            <p>
                {{ $activity->pap?->pap_code ?? 'No parent PAP' }}
                @if($activity->pap)
                <span class="activity-show-heading-separator">/</span>
                {{ $activity->pap->name }}
                @endif
            </p>
        </div>

        <div class="activity-show-header-actions">
            <a href="{{ url()->previous() }}" class="activity-show-back-btn">
                Back
            </a>

            <a href="{{ route('activities.edit', $activity) }}" class="activity-show-edit-btn">
                Edit Activity
            </a>
        </div>

    </div>

    {{-- SUCCESS MESSAGE --}}
    @if(session('success'))
    <div class="activity-show-alert">
        {{ session('success') }}
    </div>
    @endif

    {{-- SUMMARY CARDS --}}
    <div class="activity-show-summary">

        <div class="activity-show-summary-card">
            <div class="activity-show-summary-label">TOTAL TASKS</div>
            <div class="activity-show-summary-value">{{ $taskCount }}</div>
            <div class="activity-show-summary-note">Tasks under this activity</div>
        </div>

        <div class="activity-show-summary-card">
            <div class="activity-show-summary-label">ACTIVITY PROGRESS</div>
            <div class="activity-show-summary-value">{{ number_format($progress, 0) }}%</div>
            <div class="activity-show-summary-note">Based on task review progress</div>
        </div>

        <div class="activity-show-summary-card">
            <div class="activity-show-summary-label">IMPLEMENTATION STATUS</div>
            <div class="activity-show-summary-status">
                <span class="activity-show-status status-{{ strtolower($activity->status) }}">
                    {{ $activity->status }}
                </span>
            </div>
            <div class="activity-show-summary-note">Current activity status</div>
        </div>

        <div class="activity-show-summary-card">
            <div class="activity-show-summary-label">SCHEDULE</div>
            <div class="activity-show-summary-date">
                {{ $activity->start_date?->format('M d, Y') ?? 'Not set' }}
            </div>
            <div class="activity-show-summary-note">
                to {{ $activity->end_date?->format('M d, Y') ?? 'Not set' }}
            </div>
        </div>

    </div>

    {{-- MAIN CONTENT --}}
    <div class="activity-show-content-grid">

        {{-- ACTIVITY INFORMATION --}}
        <section class="activity-show-panel">

            <div class="activity-show-panel-header">
                <div>
                    <h2>Activity Information</h2>
                    <p>General details and implementation assignment</p>
                </div>
            </div>

            <div class="activity-show-information">

                <div class="activity-show-info-row">
                    <div class="activity-show-info-label">Activity Title</div>
                    <div class="activity-show-info-value">{{ $activity->title }}</div>
                </div>

                <div class="activity-show-info-row">
                    <div class="activity-show-info-label">Parent PAP</div>
                    <div class="activity-show-info-value">
                        @if($activity->pap)
                        <strong>{{ $activity->pap->pap_code }}</strong>
                        <span class="activity-show-secondary">
                            {{ $activity->pap->name }}
                        </span>
                        @else
                        Not assigned
                        @endif
                    </div>
                </div>

                <div class="activity-show-info-row">
                    <div class="activity-show-info-label">Description</div>
                    <div class="activity-show-info-value activity-show-description">
                        {{ $activity->description ?: 'No description provided.' }}
                    </div>
                </div>

                <div class="activity-show-info-row">
                    <div class="activity-show-info-label">Location</div>
                    <div class="activity-show-info-value">
                        {{ $activity->location ?: 'Not specified' }}
                    </div>
                </div>

                <div class="activity-show-info-row">
                    <div class="activity-show-info-label">Focal Person</div>
                    <div class="activity-show-info-value">
                        {{ $activity->focalPerson?->name ?? 'Not assigned' }}
                    </div>
                </div>

                <div class="activity-show-info-row">
                    <div class="activity-show-info-label">Implementation Period</div>
                    <div class="activity-show-info-value">
                        {{ $activity->start_date?->format('F d, Y') ?? 'Not set' }}
                        <span class="activity-show-secondary">to</span>
                        {{ $activity->end_date?->format('F d, Y') ?? 'Not set' }}
                    </div>
                </div>

                <div class="activity-show-info-row">
                    <div class="activity-show-info-label">Quarter</div>
                    <div class="activity-show-info-value">
                        {{ match((string) $activity->quarter) {
            '1', 'Q1' => 'First Quarter',
            '2', 'Q2' => 'Second Quarter',
            '3', 'Q3' => 'Third Quarter',
            '4', 'Q4' => 'Fourth Quarter',
            default => 'Not specified',
        } }}
                    </div>
                </div>

                <div class="activity-show-info-row">
                    <div class="activity-show-info-label">Created By</div>
                    <div class="activity-show-info-value">
                        {{ $activity->creator?->name ?? 'Not available' }}
                    </div>
                </div>

            </div>

        </section>

        {{-- PROGRESS PANEL --}}
        <aside class="activity-show-panel activity-show-progress-panel">

            <div class="activity-show-panel-header">
                <div>
                    <h2>Implementation Progress</h2>
                    <p>Overall task completion</p>
                </div>
            </div>

            <div class="activity-show-progress-content">

                <div class="activity-show-progress-number">
                    {{ number_format($progress, 0) }}<span>%</span>
                </div>

                <div class="activity-show-progress-track">
                    <div class="activity-show-progress-fill" style="width: {{ $progress }}%;">
                    </div>
                </div>

                <div class="activity-show-progress-caption">
                    @if($taskCount === 0)
                    No tasks have been assigned yet.
                    @else
                    Average progress of {{ $taskCount }}
                    {{ $taskCount === 1 ? 'task' : 'tasks' }}.
                    @endif
                </div>

                <div class="activity-show-progress-divider"></div>

                <div class="activity-show-progress-stat">
                    <span>Total Tasks</span>
                    <strong>{{ $taskCount }}</strong>
                </div>

                <div class="activity-show-progress-stat">
                    <span>Focal Person</span>
                    <strong>{{ $activity->focalPerson?->name ?? 'Unassigned' }}</strong>
                </div>

                <div class="activity-show-progress-stat">
                    <span>Quarter</span>
                    <strong>{{ match((string) $activity->quarter) {
            '1', 'Q1' => 'First Quarter',
            '2', 'Q2' => 'Second Quarter',
            '3', 'Q3' => 'Third Quarter',
            '4', 'Q4' => 'Fourth Quarter',
            default => 'Not specified',
        } }}</strong>
                </div>

            </div>

        </aside>

    </div>

    {{-- ASSOCIATED TASKS --}}
    <section class="activity-show-panel activity-show-tasks-panel">

        <div class="activity-show-panel-header activity-show-tasks-header">
            <div>
                <h2>Associated Tasks</h2>
                <p>Tasks assigned under this activity</p>
            </div>

            <span class="activity-show-task-total">
                {{ $taskCount }} {{ $taskCount === 1 ? 'Task' : 'Tasks' }}
            </span>
        </div>

        @if($activity->tasks->isEmpty())

        <div class="activity-show-empty">
            <h3>No tasks assigned</h3>
            <p>Tasks created under this activity will appear here.</p>
        </div>

        @else

        <div class="activity-show-table-wrapper">

            <table class="activity-show-task-table">

                <thead>
                    <tr>
                        <th>TASK</th>
                        <th>ASSIGNED PERSONNEL</th>
                        <th>DUE DATE</th>
                        <th>STATUS</th>
                        <th>PROGRESS</th>
                        <th class="activity-show-table-action">ACTION</th>
                    </tr>
                </thead>

                <tbody>

                    @foreach($activity->tasks as $task)

                    <tr>

                        <td>
                            <div class="activity-show-task-title">
                                {{ $task->title }}
                            </div>

                            @if($task->priority)
                            <div class="activity-show-task-priority">
                                {{ $task->priority }} Priority
                            </div>
                            @endif
                        </td>

                        <td>
                            {{ $task->assignee?->name ?? 'Not assigned' }}
                        </td>

                        <td>
                            {{ $task->due_date?->format('M d, Y') ?? 'Not set' }}
                        </td>

                        <td>
                            <span class="activity-show-task-status">
                                {{ $task->status?->name ?? 'Pending' }}
                            </span>
                        </td>

                        <td>
                            @php
                            $internalReviews = $task->reviews ?? collect();
                            $externalRoutings = $task->routings ?? collect();

                            $allReviewItems = $internalReviews->concat($externalRoutings);

                            $totalReviews = $allReviewItems->count();

                            $completedReviews = $allReviewItems->filter(function ($item) {
                            return in_array($item->status, ['Completed', 'Not Applicable']);
                            })->count();

                            $progress = $totalReviews > 0
                            ? round(($completedReviews / $totalReviews) * 100)
                            : 0;
                            @endphp

                            <div class="task-index-progress">
                                <div class="task-index-progress-info">
                                    <span>{{ $progress }}%</span>
                                    <small>{{ $completedReviews }}/{{ $totalReviews }}</small>
                                </div>

                                <div class="task-index-progress-track">
                                    <div class="task-index-progress-fill" style="width: {{ $progress }}%"></div>
                                </div>
                            </div>
                        </td>

                        <td class="activity-show-table-action">
                            <a href="{{ route('tasks.show', $task) }}" class="activity-show-task-view">
                                View Task
                            </a>
                        </td>

                    </tr>

                    @endforeach

                </tbody>

            </table>

        </div>

        @endif

    </section>

</div>

@endsection
