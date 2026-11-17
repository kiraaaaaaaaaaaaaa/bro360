@extends('layouts.app')

@section('title', 'My Tasks')
@section('page-title', 'My Tasks')
@section('page-description', 'Work assigned to the current user.')

@section('content')

<div class="page-container mytasks-page">

    {{-- PAGE HEADER --}}
    <div class="mytasks-header">
        <div>
            <h1>My tasks</h1>
            <p>Work assigned to the current user.</p>
        </div>


    </div>

    {{-- FILTERS --}}
    <div class="mytasks-filters">
        <button type="button" class="mytasks-filter active" data-filter="Open">
            Open <span>{{ $tasks->whereNotIn('status.name', ['Completed', 'Cancelled'])->count() }}</span>
        </button>

        <button type="button" class="mytasks-filter" data-filter="Due today">
            Due today
            <span>{{ $tasks->filter(fn($task) => $task->due_date && $task->due_date->isToday())->count() }}</span>
        </button>

        <button type="button" class="mytasks-filter" data-filter="Upcoming">
            Upcoming
            <span>{{ $tasks->filter(fn($task) => $task->due_date && $task->due_date->isFuture())->count() }}</span>
        </button>

        <button type="button" class="mytasks-filter" data-filter="Completed">
            Completed
            <span>{{ $tasks->filter(fn($task) => $task->status?->name === 'Completed')->count() }}</span>
        </button>
    </div>
    <div class="mytasks-notice">
        You have
        {{ $tasks->filter(fn($task) => $task->due_date && $task->due_date->between(now(), now()->addDays(7)) && $task->status?->name !== 'Completed')->count() }}
        task(s) due within the next seven days.
    </div>


    {{-- BOARD VIEW --}}
    <div id="mytasks-board-view" class="mytasks-board-view">

        @php
        $boardStatuses = [
        'Pending',
        'In Progress',
        'For Review',
        'For Revision',
        'Completed',
        ];
        @endphp

        <div class="mytasks-board">

            @foreach($boardStatuses as $boardStatus)

            <div class="mytasks-board-column">

                <div class="mytasks-board-column-header">
                    <h3>
                        {{ $boardStatus === 'For Revision' ? 'Returned Tasks' : $boardStatus }}
                    </h3>

                    <span>
                        {{ $tasks->where('status.name', $boardStatus)->count() }}
                    </span>
                </div>

                <div class="mytasks-board-cards">

                    @foreach($tasks->where('status.name', $boardStatus) as $task)

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

                    <article class="mytasks-board-card" data-status="{{ $task->status?->name ?? 'Pending' }}" data-due="{{ !$task->due_date ? 'none' : ($task->due_date->isToday() ? 'today' : ($task->due_date->isFuture() ? 'upcoming' : 'past')) }}">



                        <div class="mytasks-board-card-top">
                            <span class="mytasks-priority priority-{{ strtolower($task->priority) }}">
                                {{ $task->priority }}
                            </span>

                            <span class="mytasks-board-date">
                                {{ $task->due_date?->format('M d') ?? 'No deadline' }}
                            </span>

                        </div>

                        <h4>{{ $task->title }}</h4>

                        <p class="mytasks-board-pap">
                            {{ $task->pap?->pap_code ?? '—' }}
                            ·
                            {{ $task->pap?->name ?? 'No PAP' }}
                        </p>

                        <div class="mytasks-board-progress">
                            <div class="mytasks-board-progress-label">
                                <span>Progress</span>
                                <strong>{{ $progress }}%</strong>
                            </div>

                            <div class="mytasks-progress-track">
                                <div class="mytasks-progress-fill" style="width: {{ $progress }}%">
                                </div>
                            </div>
                        </div>

                        @if($boardStatus === 'For Revision')
                        @php
                        $returnedReview = $task->reviews
                        ->where('status', 'Returned')
                        ->sortByDesc(fn ($review) => $review->returned_at ?? $review->updated_at)
                        ->first();
                        @endphp

                        <div class="mytasks-returned-details">
                            <strong>Returned by: {{ $returnedReview?->reviewer?->name ?? 'Reviewer' }}</strong>

                            <p>
                                {{ $returnedReview?->remarks ?? 'No return remarks provided.' }}
                            </p>

                            @if($returnedReview?->returned_at)
                            <small>
                                Returned on {{ $returnedReview->returned_at->format('M d, Y h:i A') }}
                            </small>
                            @endif
                        </div>
                        @endif

                        <a href="{{ route('tasks.show', $task) }}" class="mytasks-board-view-link">
                            View task
                        </a>

                    </article>

                    @endforeach

                    @if($tasks->where('status.name', $boardStatus)->isEmpty())
                    <div class="mytasks-board-empty">
                        No tasks in this stage.
                    </div>
                    @endif

                </div>
            </div>

            @endforeach

        </div>
    </div>



</div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {

        const viewButtons = document.querySelectorAll('.mytasks-view-button');
        const filterButtons = document.querySelectorAll('.mytasks-filter');

        const boardView = document.getElementById('mytasks-board-view');

        let currentFilter = 'Open';

        function applyFilter() {
            document.querySelectorAll('.mytasks-row').forEach(row => {
                const status = row.dataset.status;
                const due = row.dataset.due;
                const completed = row.dataset.completed;

                let visible = true;

                if (currentFilter === 'Open') {
                    visible = completed !== 'yes' && status !== 'Cancelled';
                } else if (currentFilter === 'Due today') {
                    visible = due === 'today';
                } else if (currentFilter === 'Upcoming') {
                    visible = due === 'upcoming';
                } else if (currentFilter === 'Completed') {
                    visible = completed === 'yes';
                }

                row.style.display = visible ? '' : 'none';
            });

            document.querySelectorAll('.mytasks-board-card').forEach(card => {
                const status = card.dataset.status;
                const due = card.dataset.due;

                let visible = true;

                if (currentFilter === 'Open') {
                    visible = status !== 'Completed' && status !== 'Cancelled';
                } else if (currentFilter === 'Due today') {
                    visible = due === 'today';
                } else if (currentFilter === 'Upcoming') {
                    visible = due === 'upcoming';
                } else if (currentFilter === 'Completed') {
                    visible = status === 'Completed';
                }

                card.style.display = visible ? '' : 'none';
            });
        }



        filterButtons.forEach(button => {
            button.addEventListener('click', function() {
                filterButtons.forEach(item => item.classList.remove('active'));
                this.classList.add('active');

                currentFilter = this.dataset.filter;
                applyFilter();
            });
        });

    });

</script>
@endpush
