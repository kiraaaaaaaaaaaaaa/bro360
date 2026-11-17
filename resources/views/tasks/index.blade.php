@extends('layouts.app')

@section('title', 'Task Management')
@section('page-title', 'Task Management')
@section('page-description', 'Monitor, manage, and track assigned tasks and workflow progress.')

@section('content')

<div class="task-index-page">

    {{-- PAGE HEADER --}}
    <div class="task-index-header">
        <div>
            <h2>Task List</h2>
            <p>View and monitor all tasks recorded in BRO360.</p>

        </div>

        <a href="{{ route('tasks.create') }}" class="task-index-primary-btn">
            Create Task
        </a>
    </div>



    {{-- TASK TABLE --}}
    <div class="task-index-panel">


        <div class="task-index-panel-header">
            <div>
                <h3>All Tasks ({{ $tasks->total() }})</h3>
                <p>Task records and their current status.</p>
            </div>
        </div>

        <form method="GET" action="{{ route('tasks.index') }}" class="task-index-filters">

            <div class="task-index-search">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search task title or reference...">
            </div>

            <button type="submit" class="task-filter-btn">
                Search
            </button>

            <a href="{{ route('tasks.index') }}" class="task-filter-reset">
                Clear
            </a>

        </form>


        <div class="task-index-table-wrapper">
            <table class="task-index-table">
                <thead>
                    <tr>
                        <th>Task</th>
                        <th>PAP</th>
                        <th>Primary Assignee</th>
                        <th>Created By</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Progress</th>
                        <th>Due Date</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($tasks as $task)
                    <tr>
                        <td>
                            <div class="task-index-title">
                                {{ $task->title }}
                            </div>
                            <small>Task #{{ $task->id }}</small>
                        </td>

                        <td>
                            <div class="task-index-pap">
                                {{ $task->pap->pap_code ?? '—' }}
                            </div>
                            <small>{{ $task->pap->name ?? 'No PAP' }}</small>
                        </td>

                        <td>
                            {{ $task->assignee->name ?? 'Unassigned' }}
                        </td>

                        <td>
                            {{ $task->creator->name ?? '—' }}
                        </td>

                        <td>
                            <span class="task-index-priority priority-{{ strtolower($task->priority ?? 'normal') }}">
                                {{ $task->priority ?? 'Normal' }}
                            </span>
                        </td>

                        <td>
                            <span class="task-index-status">
                                {{ $task->status->name ?? $task->status ?? 'Pending' }}
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

                        <td>
                            {{ $task->due_date ? \Carbon\Carbon::parse($task->due_date)->format('M d, Y') : '—' }}
                        </td>

                        <td>
    <div class="task-index-actions">
        <a href="{{ route('tasks.show', $task) }}" class="task-index-view-btn">
            View
        </a>

        <form action="{{ route('tasks.destroy', $task) }}"
              method="POST"
              onsubmit="return confirm('Are you sure you want to delete this task? This action cannot be undone.');">
            @csrf
            @method('DELETE')

            <button type="submit" class="task-index-delete-btn">
                Delete
            </button>
        </form>
    </div>
</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="task-index-empty">
                            No tasks have been created yet.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- PAGINATION --}}
        <div class="task-index-pagination">
            {{ $tasks->links() }}
        </div>

    </div>

</div>

@endsection
