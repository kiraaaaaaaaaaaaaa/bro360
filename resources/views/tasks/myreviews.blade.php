
@extends('layouts.app')

@section('title', 'My Reviews')
@section('page-title', 'My Reviews')
@section('page-description', 'Tasks currently awaiting your review')

@section('content')

<div class="myreviews-page">

    <div class="myreviews-header">
        <div>
            <h2>Review Queue</h2>
            <p>Tasks currently assigned to you for review.</p>
        </div>

        <div class="myreviews-count">
            <strong>{{ $tasks->count() }}</strong>
            <span>Active Reviews</span>
        </div>
    </div>

    <div class="myreviews-table-wrapper">
        <table class="myreviews-table">
            <thead>
                <tr>
                    <th>Task Reference</th>
                    <th>Task Title</th>
                    <th>PAP</th>
                    <th>Priority</th>
                    <th>Review Status</th>
                    <th>Due Date</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody>
                @forelse($tasks as $task)

                    @php
                        $myReview = $task->reviews
                            ->firstWhere('reviewer_id', auth()->id());
                    @endphp

                    <tr>
                        <td>
                            {{ $task->task_code ?? 'TASK-' . str_pad($task->id, 4, '0', STR_PAD_LEFT) }}
                        </td>

                        <td>
                            <strong>{{ $task->title }}</strong>
                        </td>

                        <td>
                            {{ $task->pap->pap_code ?? '—' }}
                        </td>

                        <td>
                            {{ $task->priority }}
                        </td>

                        <td>
                            {{ $myReview->status ?? '—' }}
                        </td>

                        <td>
                            {{ $task->due_date?->format('M d, Y') ?? '—' }}
                        </td>

                        <td>
                            <a href="{{ route('tasks.show', $task->id) }}"
                               class="btn-primary">
                                Review Task
                            </a>
                        </td>
                    </tr>

                @empty

                    <tr>
                        <td colspan="7" class="myreviews-empty">
                            No tasks are currently awaiting your review.
                        </td>
                    </tr>

                @endforelse
            </tbody>
        </table>
    </div>

</div>

@endsection
