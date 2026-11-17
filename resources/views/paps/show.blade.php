@extends('layouts.app')

@section('title', 'PAP Details')
@section('page-title', 'PAP Details')
@section('page-description', 'View PAP information, activities, and assigned tasks')

@section('content')

<div class="pap-detail-page">

    {{-- HEADER --}}
    <div class="pap-detail-header">
        <div>
            <a href="{{ route('paps.index') }}" class="pap-back-link">
                ← Back to PAP Management
            </a>

            <h2>{{ $pap->name }}</h2>

            <p>{{ $pap->pap_code }}</p>
        </div>

        <div class="pap-detail-status">
            {{ $pap->status?->name ?? 'Unspecified' }}
        </div>
    </div>

    {{-- PAP INFORMATION --}}
    <div class="pap-detail-card">
        <div class="pap-detail-card-header">
            <h3>PAP Information</h3>
        </div>

        <div class="pap-detail-grid">

            <div>
                <label>Fiscal Year</label>
                <strong>{{ $pap->fiscalYear?->year ?? '—' }}</strong>
            </div>

            <div>
                <label>Division</label>
                <strong>{{ $pap->division?->name ?? '—' }}</strong>
            </div>

            <div>
                <label>Fund Source</label>
                <strong>{{ $pap->fundSource?->name ?? '—' }}</strong>
            </div>

            <div>
                <label>Program</label>
                <strong>{{ $pap->program?->name ?? '—' }}</strong>
            </div>

            <div>
                <label>Focal Person</label>
                <strong>{{ $pap->focalUser?->name ?? 'Unassigned' }}</strong>
            </div>

            <div>
                <label>Start Date</label>
                <strong>
                    {{ $pap->start_date ? \Carbon\Carbon::parse($pap->start_date)->format('M d, Y') : '—' }}
                </strong>
            </div>

            <div>
                <label>End Date</label>
                <strong>
                    {{ $pap->end_date ? \Carbon\Carbon::parse($pap->end_date)->format('M d, Y') : '—' }}
                </strong>
            </div>

            <div>
                <label>Output Category</label>
                <strong>{{ $pap->outputCategory?->name ?? '—' }}</strong>
            </div>

        </div>

        @if($pap->description)
        <div class="pap-detail-description">
            <label>Description</label>
            <p>{{ $pap->description }}</p>
        </div>
        @endif
    </div>

    {{-- SUMMARY --}}
    <div class="pap-detail-summary">

        <div class="pap-summary-card">
            <span>Total Activities</span>
            <strong>{{ $pap->activities->count() }}</strong>
        </div>

        <div class="pap-summary-card">
            <span>Total Tasks</span>
            <strong>{{ $pap->tasks->count() }}</strong>
        </div>

    </div>

    {{-- ACTIVITIES --}}
    <div class="pap-detail-card">

        <div class="pap-detail-card-header">
            <div>
                <h3>Activities</h3>
                <p>Activities registered under this PAP</p>
            </div>

            <a href="{{ route('activities.create', ['pap_id' => $pap->id]) }}" class="pap-detail-button">
                + Add Activity
            </a>
        </div>

        <div class="pap-detail-table-wrapper">
            <table class="pap-detail-table">
                <thead>
                    <tr>
                        <th>Activity</th>
                        <th>Schedule</th>
                        <th>Focal Person</th>
                        <th>Tasks</th>
                        <th>Progress</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($pap->activities as $activity)
                    <tr>
                        <td>
                            <a href="{{ route('activities.show', $activity) }}">
                                {{ $activity->title }}
                            </a>
                        </td>

                        <td>
                            {{ $activity->start_date?->format('M d, Y') ?? '—' }}
                            –
                            {{ $activity->end_date?->format('M d, Y') ?? '—' }}
                        </td>

                        <td>
                            {{ $activity->focalPerson?->name ?? 'Unassigned' }}
                        </td>

                        <td>{{ $activity->tasks_count ?? $activity->tasks->count() }}</td>

                        <td>
                            {{ number_format($activity->progress, 0) }}%
                        </td>

                        <td>{{ $activity->status }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="pap-detail-empty">
                            No activities registered under this PAP.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>

    {{-- TASKS --}}
    <div class="pap-detail-card">

        <div class="pap-detail-card-header">
            <div>
                <h3>Tasks</h3>
                <p>All tasks assigned to this PAP, including tasks not linked to an activity</p>
            </div>

            <a href="{{ route('tasks.create', ['pap_id' => $pap->id]) }}" class="pap-detail-button">
                + Create Task
            </a>
        </div>

        <div class="pap-detail-table-wrapper">
            <table class="pap-detail-table">

                <thead>
                    <tr>
                        <th>Task</th>
                        <th>Activity</th>
                        <th>Assigned Personnel</th>
                        <th>Priority</th>
                        <th>Due Date</th>
                        <th>Status</th>
                        <th>Progress</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($pap->tasks as $task)
                    <tr>
                        <td>
                            <strong>{{ $task->title }}</strong>
                        </td>

                        <td>
                            {{ $task->activity?->title ?? 'General PAP Task' }}
                        </td>

                        <td>
                            {{ $task->assignee?->name ?? 'Unassigned' }}
                        </td>

                        <td>{{ $task->priority }}</td>

                        <td>
                            {{ $task->due_date?->format('M d, Y') ?? '—' }}
                        </td>

                        <td>
                            {{ $task->status?->name ?? 'Pending' }}
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


                        <td>
                            <a href="{{ route('tasks.show', $task) }}">
                                View
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="pap-detail-empty">
                            No tasks registered under this PAP yet.
                        </td>
                    </tr>
                    @endforelse
                </tbody>

            </table>
        </div>

    </div>

</div>

@endsection
