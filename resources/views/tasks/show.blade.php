@extends('layouts.app')

@section('title', 'Task Details')
@section('page-title', 'Task Details')
@section('page-description', 'View task information, workflow, documents, and activity history')

@section('content')
@php
$userId = auth()->id();

$isAssignee = $task->assignments
->contains('user_id', $userId);

$isCreator = (int) $task->created_by === (int) $userId;

$isReviewer = $task->reviews
->contains('reviewer_id', $userId);

$canManageTask = $isAssignee || $isCreator;
$isStaffAssigneeOnly = $isAssignee && !$isReviewer && !$isCreator && $task->status?->name === 'Pending';

// WORKFLOW PROGRESS
$totalWorkflowItems = $task->reviews->count()
+ $task->routings->count();

$completedReviews = $task->reviews
->whereIn('status', ['Completed', 'Not Applicable'])
->count();

$completedRoutings = $task->routings
->where('status', 'Completed')
->count();

$completedWorkflowItems = $completedReviews + $completedRoutings;

$workflowProgress = $totalWorkflowItems > 0
? round(($completedWorkflowItems / $totalWorkflowItems) * 100)
: 0;
@endphp

<div class="page-container task-show-page">


    {{-- PAGE HEADER --}}
    <div class="task-show-header">
        <div>
            <div class="task-show-breadcrumb">
                <a href="{{ route('tasks.my') }}">My Tasks</a>
                <span>/</span>
                <span>Task Details</span>
            </div>

            <h1>{{ $task->title }}</h1>

            <p>
                {{ $task->pap->pap_code ?? 'No PAP Code' }}
                ·
                {{ $task->pap->name ?? 'No PAP Assigned' }}
            </p>
        </div>

        <div class="task-show-header-actions">

            @if($isCreator)
            <button type="button" class="task-show-btn task-show-btn-primary" onclick="openEditTaskModal()">
                Edit Task
            </button>
            @endif

            @if($isAssignee && $task->status?->name === 'Pending')
            <form action="{{ route('tasks.start', $task->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to start this task?');">
                @csrf

                <button type="submit" class="task-show-btn task-show-btn-primary">
                    Start Task
                </button>
            </form>
            @endif

            <a href="{{ url()->previous() }}" class="task-show-btn task-show-btn-secondary">
                Back
            </a>

        </div>
    </div>

    @unless($isStaffAssigneeOnly)


    {{-- TASK SUMMARY --}}
    <div class="task-show-summary-card">
        <span>Workflow Progress</span>

        <strong>{{ $workflowProgress }}%</strong>

        <div style="
        width: 100%;
        height: 8px;
        background: #e8eee9;
        border-radius: 20px;
        margin-top: 10px;
        overflow: hidden;
    ">
            <div style="
            width: {{ $workflowProgress }}%;
            height: 100%;
            background: #286747;
            border-radius: 20px;
        "></div>
        </div>

        <small style="display:block; margin-top:8px; color:#829087;">
            {{ $completedWorkflowItems }} of {{ $totalWorkflowItems }} workflow items completed
        </small>
    </div>

    @endunless

    {{-- TASK INFORMATION --}}
    <section class="task-show-panel">
        <div class="task-show-panel-header">
            <div>
                <span class="task-show-section-number">01</span>
                <h2>Task Information</h2>
            </div>
            {{-- TASK QR CODE --}}


            <div class="task-qr-container">

                <div class="task-qr-image" id="taskQrPrintArea" hidden>
                    <img src="data:image/png;base64,{{ base64_encode(
        (new \Endroid\QrCode\Writer\PngWriter())->write(
            new \Endroid\QrCode\QrCode(
                data: route('liaison.task.show', ['token' => $task->qr_review_token]),
                encoding: new \Endroid\QrCode\Encoding\Encoding('UTF-8'),
                errorCorrectionLevel: \Endroid\QrCode\ErrorCorrectionLevel::High,
                size: 300,
                margin: 10
            )
        )->getString()
    ) }}" alt="Task QR Code" style="width:190px;height:190px;object-fit:contain;">
                </div>


                <button type="button" class="btn-primary" onclick="printTaskQr()">
                    Print QR Code
                </button>


            </div>


        </div>

        <div class="task-show-info-grid">

            <div>
                <label>Task Title</label>
                <p>{{ $task->title }}</p>
            </div>

            <div>
                <label>Parent PAP</label>
                <p>{{ $task->pap->name ?? '—' }}</p>
            </div>

            <div>
                <label>Activity</label>
                <p>{{ $task->activity->title ?? 'Not linked to an activity' }}</p>
            </div>

            <div>
                <label>Created By</label>
                <p>{{ $task->creator->name ?? '—' }}</p>
            </div>

            <div class="task-show-info-full">
                <label>Description</label>
                <p>{{ $task->description ?: 'No description provided.' }}</p>
            </div>



        </div>
    </section>

    {{-- ASSIGNMENT DETAILS --}}
    <section class="task-show-panel">
        <div class="task-show-panel-header">
            <div>
                <span class="task-show-section-number">02</span>
                <h2>Task Assignment</h2>
            </div>
        </div>

        <div class="task-show-table-wrapper">
            <table class="task-show-table">
                <thead>
                    <tr>
                        <th>Personnel</th>
                        <th>Assignment</th>
                        <th>Assigned Date</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($task->assignments as $assignment)
                    <tr>
                        <td>{{ $assignment->user->name ?? 'Unknown User' }}</td>
                        <td>{{ $assignment->assignment_role }}</td>
                        <td>{{ $assignment->assigned_at?->format('M d, Y') ?? '—' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3">No assigned personnel.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    @if($isCreator || (int) $task->assigned_to === (int) auth()->id())

    <div class="task-reassignment-section">

        <h3>Reassign Primary Assignee</h3>

        <p>
            Select another staff member to become the primary assignee.
            The previous primary assignee will remain as a collaborator.
        </p>

        <form action="{{ route('tasks.reassign', $task->id) }}" method="POST">
            @csrf
            @method('PATCH')

            <div class="form-group">
                <label for="new_assignee_id">New Primary Assignee</label>

                <select name="new_assignee_id" id="new_assignee_id" required>
                    <option value="">Select Personnel</option>

                    @foreach(\App\Models\User::where('id', '!=', $task->assigned_to)->orderBy('name')->get() as $user)
                    <option value="{{ $user->id }}">
                        {{ $user->name }}
                    </option>
                    @endforeach
                </select>

                @error('new_assignee_id')
                <small class="text-danger">{{ $message }}</small>
                @enderror
            </div>

            <button type="submit" class="btn-primary" onclick="return confirm('Are you sure you want to reassign this task?');">
                Reassign Task
            </button>

        </form>

    </div>

    @endif

    @unless($isStaffAssigneeOnly)
    {{-- ATTACHMENTS --}}
    <section class="task-show-panel">
        <div class="task-show-panel-header">
            <div>
                <span class="task-show-section-number">05</span>
                <h2>Task Attachments</h2>
            </div>
        </div>

        @if($canManageTask)
        <form action="{{ route('tasks.attachments.upload', $task->id) }}" method="POST" enctype="multipart/form-data" class="task-attachment-upload">
            @csrf

            <div class="form-group">
                <label for="attachment">Upload Attachment</label>
                <input type="file" name="attachment" id="attachment" required>
                <small>Maximum file size: 20 MB.</small>
            </div>

            <button type="submit" class="btn-primary">
                Upload Attachment
            </button>
        </form>
        @endif

        <div class="task-show-table-wrapper">
            <table class="task-show-table">
                <thead>
                    <tr>
                        <th>File Name</th>
                        <th>Uploaded By</th>
                        <th>File Size</th>
                        <th>Date Uploaded</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($task->attachments as $attachment)
                    <tr>
                        <td>{{ $attachment->original_name }}</td>
                        <td>{{ $attachment->uploader->name ?? 'Unknown User' }}</td>
                        <td>
                            {{ $attachment->file_size
                                    ? number_format($attachment->file_size / 1024, 2) . ' KB'
                                    : '—' }}
                        </td>
                        <td>{{ $attachment->created_at?->format('M d, Y h:i A') ?? '—' }}</td>
                        <td>
                            @if($attachment->google_drive_file_id)

                            @if($attachment->mime_type === 'application/vnd.openxmlformats-officedocument.wordprocessingml.document')

                            <a href="https://drive.google.com/file/d/{{ $attachment->google_drive_file_id }}/view" target="_blank" rel="noopener noreferrer" class="btn-secondary">
                                Preview
                            </a>

                            @else

                            <button type="button" class="btn-secondary" onclick="openTaskAttachmentPreview(
                '{{ route('tasks.attachments.preview', $attachment->id) }}',
                @js($attachment->original_name)
            )">
                                Preview
                            </button>

                            @endif

                            <a href="{{ route('tasks.attachments.download', $attachment->id) }}" class="btn-secondary">
                                Download
                            </a>

                            @else
                            —
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5">No attachments uploaded.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    {{-- DOCUMENT FORWARDING --}}
    @if($isAssignee)

    <div class="task-section">

        <div class="task-section-header">
            <div>
                <h3>Document Forwarding</h3>
                <p>Manage the forwarding of this document through the assigned workflow.</p>
            </div>
        </div>

        @if($activeWorkflowOrder !== null)

        <div class="form-group">
            <label>Current Workflow Group</label>

            <div>
                <strong>{{ $forwardingGroupLabel }}</strong>
                <span> — Order {{ $activeWorkflowOrder }}</span>
            </div>
        </div>

        @if($canForwardDocument)

        <form action="{{ route('tasks.submit-for-review', $task->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to forward this document?');">
            @csrf

            <div class="form-group">
                <label for="forwarding_remarks">
                    Forwarding Remarks <span class="text-danger">*</span>
                </label>

                <textarea name="remarks" id="forwarding_remarks" rows="3" maxlength="2000" required placeholder="Enter forwarding remarks or instructions..."></textarea>
            </div>

            <button type="submit" class="btn-primary">
                Forward Document
            </button>
        </form>

        @else

        <p>
            This workflow group has already been forwarded.
            Wait until all members of the current group complete their actions.
        </p>

        @endif

        @else

        <p>All workflow groups have been completed.</p>

        @endif

    </div>

    @endif

    {{-- DOCUMENT REVISION AND RESUBMISSION --}}
    @if($isAssignee && $task->status?->name === 'For Revision')

    @php
    $returnedReviews = $task->reviews
    ->where('status', 'Returned')
    ->sortBy('review_order');

    $returningReviewers = $returnedReviews
    ->pluck('reviewer.name')
    ->filter()
    ->unique()
    ->implode(', ');
    @endphp

    <section class="task-show-panel">

        <div class="task-show-panel-header">
            <div>
                <span class="task-show-section-number">REVISION</span>
                <h2>Document Revision Required</h2>
            </div>

            <span class="task-show-status">For Revision</span>
        </div>

        <div class="task-show-content">

            <p>
                This document was returned by:
                <strong>{{ $returningReviewers ?: 'Assigned Reviewer' }}</strong>
            </p>

            <h4>Return Remarks</h4>

            @foreach($returnedReviews as $returnedReview)
            <div class="task-show-info-card">
                <strong>
                    {{ $returnedReview->reviewer->name ?? 'Reviewer' }}
                </strong>

                <p>
                    {{ $returnedReview->remarks ?: 'No remarks provided.' }}
                </p>

                <small>
                    Returned:
                    {{ $returnedReview->returned_at?->format('M d, Y h:i A') ?? '—' }}
                </small>
            </div>
            @endforeach

            <div class="task-show-divider"></div>

            <h4>Resubmit Revised Document</h4>

            <p>
                Upload the corrected document in the Task Attachments
                section above before resubmitting.
            </p>

            <form action="{{ route('tasks.resubmit-for-review', $task->id) }}" method="POST" onsubmit="return confirm('Resubmit the revised document to the returning reviewer?');">
                @csrf

                <div class="form-group">
                    <label for="resubmission_remarks">
                        Resubmission Remarks <span class="text-danger">*</span>
                    </label>

                    <textarea name="remarks" id="resubmission_remarks" rows="3" maxlength="2000" required placeholder="Describe the corrections made to the document..."></textarea>
                </div>

                <button type="submit" class="btn-primary">
                    Resubmit for Review
                </button>
            </form>

        </div>

    </section>

    @endif


    {{-- REVIEWER WORKFLOW --}}
    <section class="task-show-panel">

        <div class="task-show-panel-header">
            <div>
                <span class="task-show-section-number">03</span>
                <h2>Reviewer Workflow</h2>
            </div>

            <span class="task-show-count">
                {{ $task->reviews->count() }} Reviewers
            </span>
        </div>

        @php
        $reviewGroups = $task->reviews
        ->sortBy('review_order')
        ->groupBy('review_order');
        @endphp

        <div class="review-flow">

            @forelse($reviewGroups as $order => $reviews)

            @php
            $groupCompleted = $reviews->every(fn($review) =>
            in_array($review->status, ['Completed', 'Not Applicable'])
            );

            $groupReturned = $reviews->contains('status', 'Returned');

            $groupCurrent = (int) $order === (int) $activeWorkflowOrder;

            $groupStatus = $groupReturned
            ? 'Returned'
            : ($groupCompleted
            ? 'Completed'
            : ($groupCurrent && $alreadyForwarded
            ? 'In Progress'
            : 'Pending'));
            @endphp

            <div class="review-flow-group">

                <div class="review-flow-connector">
                    <span class="review-flow-node
                        {{ $groupCompleted ? 'completed' : '' }}
                        {{ $groupCurrent ? 'current' : '' }}
                        {{ $groupReturned ? 'returned' : '' }}">
                        {{ $loop->iteration }}
                    </span>

                    @if(!$loop->last)
                    <span class="review-flow-line
                            {{ $groupCompleted ? 'completed' : '' }}">
                    </span>
                    @endif
                </div>

                <div class="review-flow-cards">

                    @foreach($reviews as $review)

                    @php
                    $isCurrentReviewer =
                    (int) $review->reviewer_id === (int) auth()->id()
                    && $groupCurrent
                    && $alreadyForwarded
                    && !in_array($review->status, [
                    'Completed',
                    'Returned',
                    'Not Applicable'
                    ]);
                    @endphp

                    <div class="review-flow-card
                            {{ $isCurrentReviewer ? 'my-current-review' : '' }}
                            {{ $review->status === 'Completed' ? 'review-done' : '' }}
                            {{ $review->status === 'Returned' ? 'review-returned' : '' }}">

                        <div class="review-flow-card-top">
                            <div>
                                <strong>
                                    {{ $review->reviewer->name ?? 'Unknown Reviewer' }}
                                </strong>

                                <small>
                                    {{ $review->workflowStep->step_name ?? 'Internal Review' }}
                                </small>
                            </div>

                            <span class="review-flow-status
                                    {{ strtolower(str_replace(' ', '-', $review->status)) }}">
                                {{ $review->status }}
                            </span>
                        </div>

                        @if($review->received_at)
                        <small class="review-flow-date">
                            Received:
                            {{ $review->received_at->format('M d, Y h:i A') }}
                        </small>
                        @endif

                        @if($review->reviewed_at)
                        <small class="review-flow-date">
                            Reviewed:
                            {{ $review->reviewed_at->format('M d, Y h:i A') }}
                        </small>
                        @endif

                        @if($review->remarks)
                        <p class="review-flow-remarks">
                            {{ $review->remarks }}
                        </p>
                        @endif

                        {{-- ASSIGNEE CONTROLS --}}
                        @if($isAssignee)

                        <form action="{{ route('tasks.reviews.update-status', [$task->id, $review->id]) }}" method="POST" class="review-flow-action">
                            @csrf
                            @method('PUT')

                            <select name="status" required>
                                @foreach(['Pending', 'Received', 'Completed', 'Returned', 'Not Applicable'] as $status)
                                <option value="{{ $status }}" @selected($review->status === $status)>
                                    {{ $status }}
                                </option>
                                @endforeach
                            </select>

                            <input type="text" name="remarks" placeholder="Reason for status update" required>

                            <button type="submit" class="btn-secondary">
                                Update
                            </button>
                        </form>

                        {{-- CURRENT REVIEWER ACTION --}}
                        @elseif($isCurrentReviewer)

                        <form action="{{ route('tasks.reviews.reviewer-action', [$task->id, $review->id]) }}" method="POST" class="review-flow-action">
                            @csrf
                            @method('PUT')

                            <select name="status" required>
                                <option value="">Select Action</option>

                                @if($review->status === 'Pending')
                                <option value="Received">Mark as Received</option>
                                @endif

                                <option value="Completed">Complete Review</option>
                                <option value="Returned">Return to Assignee</option>
                            </select>

                            <input type="text" name="remarks" placeholder="Enter review remarks" required>

                            <button type="submit" class="btn-primary">
                                Submit Review
                            </button>
                        </form>

                        @endif

                    </div>

                    @endforeach

                </div>

                <div class="review-flow-group-label">
                    <strong>Sequence {{ $order }}</strong>
                    <span>{{ $groupStatus }}</span>
                </div>

            </div>

            @empty

            <p class="task-show-empty">No reviewers assigned.</p>

            @endforelse

        </div>

    </section>


    {{-- EXTERNAL ROUTING --}}
    <section class="task-show-panel">

        <div class="task-show-panel-header">
            <div>
                <span class="task-show-section-number">04</span>
                <h2>External Routing</h2>
            </div>

            <span class="task-show-count">
                {{ $task->routings->count() }} Routing Records
            </span>
        </div>

        @php
        $routingGroups = $task->routings
        ->sortBy('review_order')
        ->groupBy('review_order');
        @endphp

        @if($routingGroups->isNotEmpty())

        <div class="routing-flow">

            @foreach($routingGroups as $order => $routingGroup)

            <div class="routing-flow-group">



                <div class="routing-flow-cards">

                    @foreach($routingGroup as $routing)

                    <div class="routing-flow-card">

                        <div class="routing-flow-card-header">
                            <span class="routing-flow-type">
                                {{ ucfirst($routing->destination_type) }}
                            </span>

                            <span class="task-show-status">
                                {{ $routing->status }}
                            </span>
                        </div>

                        <h3>
                            @if($routing->destination_type === 'division')
                            {{ $routing->destinationDivision->name ?? 'Division unavailable' }}
                            @else
                            {{ $routing->destinationOffice->name ?? 'Office unavailable' }}
                            @endif
                        </h3>

                        <div class="routing-flow-details">

                            <div>
                                <span>Receiving Personnel</span>
                                <strong>{{ $routing->receiving_name ?? 'Not yet received' }}</strong>
                            </div>

                            <div>
                                <span>Received Date</span>
                                <strong>
                                    {{ $routing->received_at?->format('M d, Y h:i A') ?? '—' }}
                                </strong>
                            </div>

                            <div>
                                <span>Dispatch Date</span>
                                <strong>
                                    {{ $routing->dispatched_at?->format('M d, Y h:i A') ?? '—' }}
                                </strong>
                            </div>

                        </div>

                        @if($routing->receiving_remarks || $routing->remarks)
                        <div class="routing-flow-remarks">
                            <span>Remarks</span>
                            <p>{{ $routing->receiving_remarks ?? $routing->remarks }}</p>
                        </div>
                        @endif

                        @if($isAssignee)

                        <div class="routing-flow-actions">

                            <form action="{{ route('tasks.routings.update-status', [$task->id, $routing->id]) }}" method="POST" class="task-review-status-form">
                                @csrf
                                @method('PUT')

                                <select name="status" required>
                                    @foreach([
                                    'Pending',
                                    'Dispatched',
                                    'Received',
                                    'Completed',
                                    'Returned',
                                    'For Revision',
                                    'On Hold',
                                    'Cancelled'
                                    ] as $status)

                                    <option value="{{ $status }}" {{ $routing->status === $status ? 'selected' : '' }}>
                                        {{ $status }}
                                    </option>

                                    @endforeach
                                </select>

                                <input type="text" name="remarks" placeholder="Reason for status update" required>

                                <button type="submit" class="btn-secondary">
                                    Update Status
                                </button>

                            </form>

                        </div>

                        @else

                        <div class="routing-flow-view-only">
                            View only
                        </div>

                        @endif

                    </div>

                    @endforeach

                </div>

            </div>

            @endforeach

        </div>

        @else

        <div class="task-show-empty">
            No external routing records.
        </div>

        @endif

    </section>




    {{-- TASK HISTORY MODAL --}}
    <section class="task-show-panel task-history-trigger-panel">

        <div class="task-history-trigger">
            <div>
                <h2>Task History</h2>
                <p>View the complete record of task activities and updates.</p>
            </div>

            <button type="button" class="task-history-open-btn" onclick="openTaskHistoryModal()">
                View History
                <span>{{ $task->activityLogs->count() + $task->routings->sum(fn($routing) => $routing->logs->count()) }}</span>
            </button>
        </div>

    </section>
    @endunless

    {{-- HISTORY MODAL --}}
    <div id="taskHistoryModal" class="task-history-modal">

        <div class="task-history-modal-content">

            <div class="task-history-modal-header">
                <div>
                    <h2>Task Activity History</h2>
                    <p>{{ $task->title }}</p>
                </div>

                <button type="button" class="task-history-close" onclick="closeTaskHistoryModal()">
                    &times;
                </button>
            </div>


            <div class="task-history-modal-body">

                @php
                $historyItems = collect();

                // Add internal task activity logs.
                foreach ($task->activityLogs as $log) {
                $historyItems->push([
                'type' => 'task',
                'log' => $log,
                'routing' => null,
                ]);
                }

                // Add external routing logs.
                foreach ($task->routings as $routing) {
                foreach ($routing->logs as $log) {
                $historyItems->push([
                'type' => 'routing',
                'log' => $log,
                'routing' => $routing,
                ]);
                }
                }

                // Sort all history entries chronologically.
                $historyItems = $historyItems->sortBy(
                fn ($item) => $item['log']->created_at
                );
                @endphp

                @forelse($historyItems as $item)

                @php
                $log = $item['log'];
                $routing = $item['routing'];
                $details = $log->details ?? [];
                @endphp

                <div class="task-show-history-item">

                    <div>
                        @if($item['type'] === 'routing')

                        <strong>External Routing: {{ $log->action }}</strong>

                        <p>
                            {{ $routing->destination_type === 'division'
                            ? ($routing->destinationDivision->name ?? 'Division')
                            : ($routing->destinationOffice->name ?? 'Office') }}
                        </p>

                        <p>
                            <strong>Previous Status:</strong>
                            {{ $log->previous_status ?? 'N/A' }}
                        </p>

                        <p>
                            <strong>New Status:</strong>
                            {{ $log->new_status ?? 'N/A' }}
                        </p>

                        @if(data_get($details, 'receiving_name'))
                        <p>
                            <strong>Receiving Name:</strong>
                            {{ data_get($details, 'receiving_name') }}
                        </p>
                        @endif

                        @if($log->remarks)
                        <p>
                            <strong>Remarks:</strong>
                            {{ $log->remarks }}
                        </p>
                        @endif

                        @if(data_get($details, 'photo_uploaded'))
                        <p>
                            <strong>Photo Proof:</strong>
                            Uploaded
                        </p>
                        @endif

                        <small>
                            {{ $log->user->name ?? 'Liaison / External User' }}
                        </small>

                        @else

                        <strong>{{ $log->action }}</strong>

                        <p>{{ $log->user->name ?? 'System' }}</p>

                        @if($log->details)
                        <small>
                            {{ is_array($log->details)
                                ? json_encode($log->details, JSON_UNESCAPED_UNICODE)
                                : $log->details }}
                        </small>
                        @endif

                        @endif
                    </div>

                    <span>
                        {{ $log->created_at?->format('M d, Y h:i A') }}
                    </span>

                </div>

                @empty

                <p class="task-show-empty">No task history recorded.</p>

                @endforelse

            </div>


            <div class="task-history-modal-footer">
                <button type="button" class="btn-secondary" onclick="closeTaskHistoryModal()">
                    Close
                </button>
            </div>

        </div>
    </div>

    {{-- TASK ATTACHMENT PREVIEW MODAL --}}
    <div class="document-modal" id="taskAttachmentPreviewModal">

        <div class="document-modal-content preview-modal-content">

            <div class="document-modal-header">
                <div>
                    <h2 id="taskAttachmentPreviewTitle">Attachment Preview</h2>
                    <p>Viewing task attachment inside BRO360.</p>
                </div>

                <button type="button" class="document-modal-close" onclick="closeTaskAttachmentPreview()">
                    &times;
                </button>
            </div>

            <div class="document-preview-container">
                <iframe id="taskAttachmentPreviewFrame" src="" title="Task Attachment Preview">
                </iframe>
            </div>

        </div>
    </div>

    {{-- EDIT TASK MODAL --}}
    <div class="task-edit-modal" id="editTaskModal">
        <div class="task-edit-modal-content">

            <div class="task-edit-modal-header">
                <div>
                    <h2>Edit Task</h2>
                    <p>Update task information and assignments.</p>
                </div>

                <button type="button" class="task-edit-modal-close" onclick="closeEditTaskModal()">
                    &times;
                </button>
            </div>

            <form action="{{ route('tasks.update', $task->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="task-edit-modal-body">

                    {{-- TASK DETAILS --}}
                    <div class="task-form-section">
                        <div class="task-form-section-heading">
                            Task Details
                        </div>

                        <div class="task-form-grid">

                            <div class="task-form-field">
    <label for="edit_task_type_id">Task Title</label>

    <select name="task_type_id" id="edit_task_type_id" required>
        @foreach($taskTypes as $taskType)
            <option value="{{ $taskType->id }}"
                    data-workflow-name="{{ $taskType->workflowTemplate?->name ?? 'No workflow assigned' }}"
                {{ old('task_type_id', $task->task_type_id) == $taskType->id ? 'selected' : '' }}>
                {{ $taskType->title }}
            </option>
        @endforeach
    </select>

    @error('task_type_id')
        <span class="task-form-error">{{ $message }}</span>
    @enderror
</div>

<div class="task-form-field">
    <label>Assigned Workflow</label>

    <input type="text"
           id="edit_workflow_name"
           value="{{ $task->taskType?->workflowTemplate?->name ?? 'No workflow assigned' }}"
           readonly>
</div>

                            <div class="task-form-field task-form-full">
                                <label for="edit_pap_id">Program / Activity / Project</label>

                                <select name="pap_id" id="edit_pap_id" required>
                                    @foreach($paps as $pap)
                                    <option value="{{ $pap->id }}" {{ old('pap_id', $task->pap_id) == $pap->id ? 'selected' : '' }}>
                                        {{ $pap->pap_code }} - {{ $pap->name }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('pap_id')
                                <span class="task-form-error">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="task-form-field task-form-full">
                                <label for="edit_pap_activity_id">Activity</label>

                                <select name="pap_activity_id" id="edit_pap_activity_id">
                                    <option value="">General PAP Task (No Activity)</option>
                                </select>
                                @error('pap_activity_id')
                                <span class="task-form-error">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="task-form-field task-form-full">
                                <label for="edit_description">Description</label>

                                <textarea name="description" id="edit_description" rows="4">{{ old('description', $task->description) }}</textarea>
                            </div>

                            <div class="task-form-field">
                                <label for="edit_priority">Priority</label>

                                <select name="priority" id="edit_priority" required>
                                    @foreach(['Low', 'Normal', 'High', 'Urgent'] as $priority)
                                    <option value="{{ $priority }}" {{ old('priority', $task->priority) === $priority ? 'selected' : '' }}>
                                        {{ $priority }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('priority')
                                <span class="task-form-error">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="task-form-field">
                                <label for="edit_start_date">Start Date</label>

                                <input type="date" name="start_date" id="edit_start_date" value="{{ old('start_date', $task->start_date ? \Carbon\Carbon::parse($task->start_date)->format('Y-m-d') : '') }}">
                                @error('start_date')
                                <span class="task-form-error">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="task-form-field">
                                <label for="edit_due_date">Due Date</label>

                                <input type="date" name="due_date" id="edit_due_date" value="{{ old('due_date', $task->due_date ? \Carbon\Carbon::parse($task->due_date)->format('Y-m-d') : '') }}">
                                @error('due_date')
                                <span class="task-form-error">{{ $message }}</span>
                                @enderror
                            </div>

                        </div>
                    </div>

                    {{-- TASK ASSIGNMENT --}}
                    <div class="task-form-section">
                        <div class="task-form-section-heading">
                            Task Assignment
                        </div>

                        <div class="task-form-grid">

                            <div class="task-form-field task-form-full">
                                <label for="edit_assigned_users">Assigned Personnel</label>

                                <select name="assigned_users[]" id="edit_assigned_users" multiple required>
                                    @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ in_array($user->id, old('assigned_users', $task->assignments->pluck('user_id')->all())) ? 'selected' : '' }}>
                                        {{ $user->name }}
                                    </option>
                                    @endforeach
                                </select>

                                <small>Select all personnel assigned to this task.</small>

                                @error('assigned_users')
                                <span class="task-form-error">{{ $message }}</span>
                                @enderror
                                @error('assigned_users.*')
                                <span class="task-form-error">{{ $message }}</span>
                                @enderror
                            </div>

                            <div class="task-form-field task-form-full">
                                <label for="edit_primary_assignee_id">Primary Assignee</label>

                                <select name="primary_assignee_id" id="edit_primary_assignee_id" required>
                                    @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ old('primary_assignee_id', $task->assigned_to) == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }}
                                    </option>
                                    @endforeach
                                </select>

                                @error('primary_assignee_id')
                                <span class="task-form-error">{{ $message }}</span>
                                @enderror
                            </div>

                        </div>
                    </div>

                </div>

                <div class="task-edit-modal-footer">
                    <button type="button" class="task-show-btn" onclick="closeEditTaskModal()">
                        Cancel
                    </button>

                    <button type="submit" class="task-show-btn task-show-btn-primary">
                        Save Changes
                    </button>
                </div>

            </form>
        </div>
    </div>

</div>

<script>
    function openTaskHistoryModal() {
        document.getElementById('taskHistoryModal').classList.add('active');
    }

    function closeTaskHistoryModal() {
        document.getElementById('taskHistoryModal').classList.remove('active');
    }

    document.getElementById('taskHistoryModal').addEventListener('click', function(event) {
        if (event.target === this) {
            closeTaskHistoryModal();
        }
    });

    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeTaskHistoryModal();
        }
    });

</script>

<script>
    function printTaskQr() {
        const qrImage = document.querySelector('#taskQrPrintArea img');

        if (!qrImage || !qrImage.complete || qrImage.naturalWidth === 0) {
            alert('QR code is not ready for printing. Please try again.');
            return;
        }

        const printWindow = window.open('', '_blank', 'width=600,height=700');

        if (!printWindow) {
            alert('Please allow pop-ups to print the QR code.');
            return;
        }

        printWindow.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>Task QR Code</title>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        text-align: center;
                        padding: 40px;
                        color: #153b2a;
                    }

                    h2 {
                        font-size: 20px;
                        margin-bottom: 8px;
                    }

                    p {
                        font-size: 12px;
                        color: #555;
                        margin-bottom: 20px;
                    }

                    img {
                        width: 280px;
                        height: 280px;
                    }

                    @media print {
                        @page {
                            size: A5 portrait;
                            margin: 15mm;
                        }
                    }
                </style>
            </head>
            <body>
                <h2>{{ addslashes($task->title) }}</h2>
                <p>Scan this QR code to access the task's Liaison Officer page.</p>
                <img src="${qrImage.src}" alt="Task QR Code">
            </body>
            </html>
        `);

        printWindow.document.close();

        printWindow.onload = function() {
            printWindow.focus();
            printWindow.print();
            printWindow.onafterprint = function() {
                printWindow.close();
            };
        };
    }

    function openTaskAttachmentPreview(url, filename) {
        document.getElementById('taskAttachmentPreviewTitle').textContent = filename;

        document.getElementById('taskAttachmentPreviewFrame').src = url;

        document.getElementById('taskAttachmentPreviewModal').classList.add('show');
    }

    function closeTaskAttachmentPreview() {
        document.getElementById('taskAttachmentPreviewModal').classList.remove('show');

        document.getElementById('taskAttachmentPreviewFrame').src = '';
    }

</script>
<script>
    function openEditTaskModal() {
        document.getElementById('editTaskModal').classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeEditTaskModal() {
        document.getElementById('editTaskModal').classList.remove('active');
        document.body.style.overflow = '';
    }

    document.addEventListener('DOMContentLoaded', function() {

        const modal = document.getElementById('editTaskModal');
        const papSelect = document.getElementById('edit_pap_id');
        const activitySelect = document.getElementById('edit_pap_activity_id');
        const taskTypeSelect = document.getElementById('edit_task_type_id');
const workflowName = document.getElementById('edit_workflow_name');

        const papActivities = @json(
            $paps->mapWithKeys(function($pap) {
                return [
                    $pap->id => $pap->activities->map(function($activity) {
                        return [
                            'id' => $activity->id
                            , 'title' => $activity->title
                        , ];
                    })->values()
                ];
            })
        );

        const selectedActivity = @json(
            old('pap_activity_id', $task -> pap_activity_id)
        );

        function loadActivities(papId, selectedId = null) {
            activitySelect.innerHTML = '';

            const defaultOption = document.createElement('option');
            defaultOption.value = '';
            defaultOption.textContent = 'General PAP Task (No Activity)';
            activitySelect.appendChild(defaultOption);

            const activities = papActivities[papId] || [];

            activities.forEach(function(activity) {
                const option = document.createElement('option');
                option.value = activity.id;
                option.textContent = activity.title;

                if (String(activity.id) === String(selectedId)) {
                    option.selected = true;
                }

                activitySelect.appendChild(option);
            });
        }

        function updateWorkflowDisplay() {
    const selectedOption = taskTypeSelect.options[taskTypeSelect.selectedIndex];

    workflowName.value = selectedOption
        ? selectedOption.dataset.workflowName
        : 'No workflow assigned';
}

taskTypeSelect.addEventListener('change', updateWorkflowDisplay);

// Set the correct workflow when the modal loads
updateWorkflowDisplay();

        papSelect.addEventListener('change', function() {
            loadActivities(this.value);
        });

        loadActivities(papSelect.value, selectedActivity);

        modal.addEventListener('click', function(event) {
            if (event.target === modal) {
                closeEditTaskModal();
            }
        });

        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape' && modal.classList.contains('active')) {
                closeEditTaskModal();
            }
        });

        @if($errors -> hasAny([
            'pap_id'
            , 'pap_activity_id'
            , 'description'
            , 'priority'
            , 'assigned_users'
            , 'primary_assignee_id'
            , 'start_date'
            , 'due_date'
        ]))
        openEditTaskModal();
        @endif
    });

</script>





@endsection
