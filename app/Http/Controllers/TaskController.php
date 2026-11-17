<?php

namespace App\Http\Controllers;



use App\Models\Task;
use App\Models\Pap;
use App\Models\User;
use App\Models\TaskStatus;
use App\Models\TaskReview;
use App\Models\TaskAssignment;
use App\Models\TaskActivityLog;
use App\Models\WorkflowTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;
use App\Models\TaskRouting;
use App\Notifications\TaskSubmittedForReview;
use App\Models\TaskAttachment;
use App\Services\GoogleDriveService;
use App\Models\TaskRoutingLog;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use App\Notifications\TaskAssigned;
use App\Notifications\TaskReassigned;
use App\Models\TaskType;




class TaskController extends Controller
{
    /**
     * Display the task listing.
     */


    public function index(Request $request)
    {
        $tasks = Task::with([
            'pap',
            'assignee',
            'creator',
            'status',
            'reviews',
            'routings',
        ])
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim($request->search);

                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%");

                    if (is_numeric($search)) {
                        $q->orWhere('id', $search);
                    }
                });
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('tasks.index', compact('tasks'));
    }


    /**
     * Display the task creation form.
     */
    public function create(Request $request)
    {
        $paps = Pap::with('activities')
            ->orderBy('name')
            ->get();

        $users = User::orderBy('name')->get();

        $taskTypes = TaskType::with('workflowTemplate')
            ->where('is_active', true)
            ->whereHas('workflowTemplate', function ($query) {
                $query->where('process_type', 'task')
                    ->where('is_active', true);
            })
            ->orderBy('title')
            ->get();

        $workflowTemplates = WorkflowTemplate::where('process_type', 'task')
            ->where('is_active', true)
            ->with('steps.assignees')
            ->orderBy('name')
            ->get();

        $selectedPapId = $request->query('pap_id');
        $selectedActivityId = $request->query('activity_id');

        return view('tasks.create', compact(
            'paps',
            'users',
            'workflowTemplates',
            'selectedPapId',
            'selectedActivityId',
            'taskTypes'
        ));
    }

    /**
     * Display tasks assigned to the logged-in user.
     */
    public function myTasks()
    {
        $tasks = Task::with([
            'pap',
            'activity',
            'assignee',
            'status',
            'assignments',
            'reviews.reviewer',
            'reviews',
            'routings',
        ])
            ->whereHas('assignments', function ($query) {
                $query->where('user_id', Auth::id());
            })
            ->latest()
            ->get();

        return view('tasks.mytasks', compact('tasks'));
    }

    /**
     * Store a new task and generate its reviewer sequence.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'pap_id' => [
                'required',
                'exists:paps,id',
            ],

            'pap_activity_id' => [
                'nullable',
                'exists:pap_activities,id',
            ],



            'description' => [
                'nullable',
                'string',
            ],

            'priority' => [
                'required',
                Rule::in(['Low', 'Normal', 'High', 'Urgent']),
            ],

            'assigned_users' => [
                'required',
                'array',
                'min:1',
            ],

            'assigned_users.*' => [
                'required',
                'integer',
                'distinct',
                'exists:users,id',
            ],

            'primary_assignee_id' => [
                'required',
                'integer',
                Rule::in($request->input('assigned_users', [])),
            ],

            'start_date' => [
                'nullable',
                'date',
            ],

            'due_date' => [
                'nullable',
                'date',
                'after_or_equal:start_date',
            ],

            'task_type_id' => [
                'required',
                'integer',
                Rule::exists('task_types', 'id')
                    ->where('is_active', true),
            ],


        ]);

        $taskType = TaskType::with('workflowTemplate')
            ->where('is_active', true)
            ->findOrFail($validated['task_type_id']);

        $workflowTemplate = $taskType->workflowTemplate;

        if (
            !$workflowTemplate ||
            !$workflowTemplate->is_active ||
            $workflowTemplate->process_type !== 'task'
        ) {
            throw ValidationException::withMessages([
                'task_type_id' => 'The selected task type does not have a valid active workflow.',
            ]);
        }

        // Automatically determine the task title and workflow.
        $validated['title'] = $taskType->title;
        $validated['workflow_template_id'] = $workflowTemplate->id;

        // Make sure the selected activity belongs to the selected PAP.
        if (!empty($validated['pap_activity_id'])) {
            $activityBelongsToPap = DB::table('pap_activities')
                ->where('id', $validated['pap_activity_id'])
                ->where('pap_id', $validated['pap_id'])
                ->exists();

            if (!$activityBelongsToPap) {
                throw ValidationException::withMessages([
                    'pap_activity_id' => 'The selected activity does not belong to the selected PAP.',
                ]);
            }
        }

        // Retrieve the Pending task status.
        $pendingStatus = TaskStatus::where('name', 'Pending')->first();

        if (!$pendingStatus) {
            throw ValidationException::withMessages([
                'workflow_template_id' => 'The Pending task status is not configured.',
            ]);
        }

        // Load the selected workflow and its ordered reviewer groups.
        $workflow = WorkflowTemplate::with([
            'steps' => function ($query) {
                $query->orderBy('step_order')
                    ->with([
                        'assignees' => function ($assigneeQuery) {
                            $assigneeQuery->orderBy('review_order');
                        },
                    ]);
            },
        ])->findOrFail($validated['workflow_template_id']);

        if ($workflow->steps->isEmpty()) {
            throw ValidationException::withMessages([
                'workflow_template_id' => 'The selected workflow has no configured steps.',
            ]);
        }

        return DB::transaction(function () use (
            $validated,
            $pendingStatus,
            $workflow,
            $taskType
        ) {
            // Create the task.
            $task = Task::create([
                'pap_id' => $validated['pap_id'],
                'pap_activity_id' => $validated['pap_activity_id'] ?? null,
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'assigned_to' => $validated['primary_assignee_id'],
                'created_by' => Auth::id(),
                'status_id' => $pendingStatus->id,
                'priority' => $validated['priority'],
                'start_date' => $validated['start_date'] ?? null,
                'due_date' => $validated['due_date'] ?? null,
                'qr_review_token' => bin2hex(random_bytes(32)),
                'task_type_id' => $taskType->id,
                'workflow_template_id' => $validated['workflow_template_id'],
            ]);

            // Send notifications to all assigned users after the task is committed.
            DB::afterCommit(function () use ($validated, $task) {

                $assignerName = Auth::user()?->name ?? 'BRO360';

                $assignedUsers = User::whereIn(
                    'id',
                    $validated['assigned_users']
                )->get();

                foreach ($assignedUsers as $assignedUser) {
                    $assignedUser->notify(
                        new TaskAssigned($task, $assignerName)
                    );
                }
            });

            // Create task assignments.
            foreach ($validated['assigned_users'] as $userId) {
                TaskAssignment::create([
                    'task_id' => $task->id,
                    'user_id' => $userId,
                    'assignment_role' =>
                    (int) $userId === (int) $validated['primary_assignee_id']
                        ? 'Primary'
                        : 'Assignee',
                    'assigned_by' => Auth::id()
                ]);
            }

            $globalReviewOrder = 0;
            $workflowGroupsCreated = 0;
            $internalReviewGroupsCreated = 0;
            $externalRoutingGroupsCreated = 0;

            $seenReviewers = [];

            // Process workflow steps in their configured order.
            foreach ($workflow->steps as $step) {

                // Group assignment targets by configured review order.
                $reviewerGroups = $step->assignees->groupBy('review_order');

                // Support legacy direct assignments.
                if ($reviewerGroups->isEmpty()) {

                    if ($step->assignment_type && $step->assignment_id) {

                        $reviewerGroups = collect([
                            1 => collect([
                                (object) [
                                    'assignment_type' => $step->assignment_type,
                                    'assignment_id' => $step->assignment_id,
                                ],
                            ]),
                        ]);
                    } elseif ($step->role_id) {

                        $reviewerGroups = collect([
                            1 => collect([
                                (object) [
                                    'assignment_type' => 'role',
                                    'assignment_id' => $step->role_id,
                                ],
                            ]),
                        ]);
                    }
                }

                if ($reviewerGroups->isEmpty() && $step->is_required) {
                    throw ValidationException::withMessages([
                        'workflow_template_id' =>
                        "No assignments are configured for {$step->step_name}.",
                    ]);
                }

                // Process each assignment group.
                foreach ($reviewerGroups as $groupAssignments) {

                    $groupReviewers = collect();
                    $externalDestinations = collect();

                    foreach ($groupAssignments as $assignment) {

                        $assignmentType = $assignment->assignment_type;
                        $assignmentId = (int) $assignment->assignment_id;

                        /*
             * EXTERNAL ROUTING
             * Division and Office do not require individual users.
             */
                        if (in_array($assignmentType, ['division', 'office'], true)) {

                            $destinationTable = $assignmentType === 'division'
                                ? 'divisions'
                                : 'offices';

                            $destinationExists = DB::table($destinationTable)
                                ->where('id', $assignmentId)
                                ->exists();

                            if (!$destinationExists) {
                                throw ValidationException::withMessages([
                                    'workflow_template_id' =>
                                    "The selected {$assignmentType} destination ID {$assignmentId} does not exist.",
                                ]);
                            }

                            $externalDestinations->push([
                                'type' => $assignmentType,
                                'id' => $assignmentId,
                            ]);

                            continue;
                        }

                        /*
             * INTERNAL REVIEW
             * Existing user and role resolution remains unchanged.
             */
                        $resolvedUsers = $this->resolveReviewers(
                            $assignmentType,
                            $assignmentId
                        );

                        if ($resolvedUsers->isEmpty()) {
                            throw ValidationException::withMessages([
                                'workflow_template_id' =>
                                "No users were found for {$assignmentType} assignment ID {$assignmentId} in {$step->step_name}.",
                            ]);
                        }

                        $groupReviewers = $groupReviewers->merge($resolvedUsers);
                    }

                    // Remove duplicate internal reviewers.
                    $groupReviewers = $groupReviewers
                        ->unique('id')
                        ->reject(function ($reviewer) use (&$seenReviewers) {
                            return isset($seenReviewers[$reviewer->id]);
                        })
                        ->values();

                    // Remove duplicate external destinations within this group.
                    $externalDestinations = $externalDestinations
                        ->unique(function ($destination) {
                            return $destination['type'] . '-' . $destination['id'];
                        })
                        ->values();

                    // Skip only if this group has neither internal nor external targets.
                    if (
                        $groupReviewers->isEmpty() &&
                        $externalDestinations->isEmpty()
                    ) {
                        continue;
                    }

                    $globalReviewOrder++;

                    // The first internal reviewer group is activated immediately.
                    $groupStatus = 'Pending';

                    /*
         * CREATE INTERNAL REVIEW RECORDS
         */
                    foreach ($groupReviewers as $reviewer) {

                        TaskReview::create([
                            'task_id' => $task->id,
                            'reviewer_id' => $reviewer->id,
                            'workflow_step_id' => $step->id,
                            'review_order' => $globalReviewOrder,
                            'status' => $groupStatus,
                            'received_at' => null,
                        ]);

                        $seenReviewers[$reviewer->id] = true;
                    }

                    if ($groupReviewers->isNotEmpty()) {
                        $internalReviewGroupsCreated++;
                    }

                    /*
         * CREATE EXTERNAL ROUTING RECORDS
         */
                    foreach ($externalDestinations as $destination) {

                        TaskRouting::create([
                            'task_id' => $task->id,
                            'workflow_step_id' => $step->id,
                            'destination_type' => $destination['type'],
                            'destination_id' => $destination['id'],
                            'review_order' => $globalReviewOrder,
                            'status' => 'Pending',
                            'qr_token' => bin2hex(random_bytes(32)),
                        ]);

                        $externalRoutingGroupsCreated++;
                    }

                    $workflowGroupsCreated++;
                }
            }

            if ($workflowGroupsCreated === 0) {
                throw ValidationException::withMessages([
                    'workflow_template_id' =>
                    'The selected workflow did not produce any valid internal or external assignments.',
                ]);
            }

            // Record task creation.
            TaskActivityLog::create([
                'task_id' => $task->id,
                'user_id' => Auth::id(),
                'action' => 'Created',
                'details' => [
                    'workflow_template_id' => $workflow->id,
                    'workflow_name' => $workflow->name,
                    'workflow_groups_created' => $workflowGroupsCreated,
                    'internal_review_groups_created' => $internalReviewGroupsCreated,
                    'external_routing_groups_created' => $externalRoutingGroupsCreated,
                ],
            ]);

            return redirect()
                ->route('tasks.index')
                ->with('success', 'Task created successfully with its reviewer workflow.');
        });
    }


    public function show(Task $task)
    {
        $userId = Auth::id();

        $taskTypes = TaskType::with('workflowTemplate')
            ->where('is_active', true)
            ->orderBy('title')
            ->get();

        // Load task information and related records.
        $task->load([
            'pap.fiscalYear',
            'pap.division',
            'pap.fundSource',
            'activity',
            'assignee',
            'creator',
            'status',
            'assignments.user',
            'reviews.reviewer',
            'reviews.workflowStep',
            'comments.user',
            'attachments.uploader',
            'activityLogs.user',
            'routings.workflowStep',
            'routings.dispatcher',
            'routings.actionBy',
            'routings.destinationDivision',
            'routings.destinationOffice',
            'routings.logs.user',
            'taskType',
            'workflowTemplate',
        ]);

        // Check whether the current user is an assignee.
        $isAssignee = $task->assignments->contains('user_id', $userId);

        // Collect all workflow orders from internal and external assignments.
        $workflowOrders = $task->reviews
            ->pluck('review_order')
            ->merge($task->routings->pluck('review_order'))
            ->filter(fn($order) => $order !== null)
            ->unique()
            ->sort()
            ->values();

        $activeWorkflowOrder = null;
        $activeGroupHasInternal = false;
        $activeGroupHasExternal = false;
        $alreadyForwarded = false;

        // Find the first unfinished workflow group.
        foreach ($workflowOrders as $order) {

            $groupReviews = $task->reviews
                ->where('review_order', $order);

            $groupRoutings = $task->routings
                ->where('review_order', $order);

            // Determine the completion rule for the internal review group.
            $completionRule = $groupReviews->first()
                ?->workflowStep
                ?->completion_rule ?? 'all';

            // Check internal review completion.
            if ($groupReviews->isEmpty()) {

                $internalComplete = true;
            } elseif ($completionRule === 'any') {

                $internalComplete = $groupReviews->contains(function ($review) {
                    return in_array($review->status, [
                        'Completed',
                        'Not Applicable',
                    ]);
                });
            } else {

                $internalComplete = $groupReviews->every(function ($review) {
                    return in_array($review->status, [
                        'Completed',
                        'Not Applicable',
                    ]);
                });
            }

            // Check external routing completion.
            $externalComplete = $groupRoutings->every(function ($routing) {
                return in_array($routing->status, [
                    'Completed',
                    'Not Applicable',
                ]);
            });

            // Identify the first unfinished workflow group.
            if (!$internalComplete || !$externalComplete) {

                $activeWorkflowOrder = $order;

                $activeGroupHasInternal = $groupReviews->isNotEmpty();
                $activeGroupHasExternal = $groupRoutings->isNotEmpty();

                // Check whether this group has already been forwarded.
                $alreadyForwarded = $task->activityLogs->contains(
                    function ($log) use ($order) {
                        return $log->action === 'Workflow Group Forwarded'
                            && (int) data_get($log->details, 'review_order')
                            === (int) $order;
                    }
                );

                break;
            }
        }

        // Determine whether the assignee can forward the document.
        $canForwardDocument =
            $isAssignee &&
            $activeWorkflowOrder !== null &&
            !$alreadyForwarded;

        // Identify the type of the active workflow group.
        if ($activeGroupHasInternal && $activeGroupHasExternal) {
            $forwardingGroupLabel = 'Internal Review and External Routing';
        } elseif ($activeGroupHasInternal) {
            $forwardingGroupLabel = 'Internal Review';
        } elseif ($activeGroupHasExternal) {
            $forwardingGroupLabel = 'External Routing';
        } else {
            $forwardingGroupLabel = 'Workflow Completed';
        }

        // Load data needed by the Edit Task modal.
        $paps = Pap::with('activities')
            ->orderBy('name')
            ->get();

        $users = User::orderBy('name')->get();

        return view('tasks.show', compact(
            'task',
            'isAssignee',
            'activeWorkflowOrder',
            'activeGroupHasInternal',
            'activeGroupHasExternal',
            'alreadyForwarded',
            'canForwardDocument',
            'forwardingGroupLabel',
            'paps',
            'users',
            'taskTypes'
        ));
    }


    /**
     * Submit a task for the first reviewer group.
     */

    public function submitForReview(Request $request, Task $task)
    {
        // Only assigned personnel may forward the document.
        $isAssigned = TaskAssignment::where('task_id', $task->id)
            ->where('user_id', Auth::id())
            ->exists();

        if (!$isAssigned) {
            abort(403, 'You are not authorized to forward this document.');
        }

        $validated = $request->validate([
            'remarks' => 'required|string|max:2000',
        ]);

        $senderName = Auth::user()->name;

        $result = DB::transaction(function () use ($task, $validated, $senderName) {

            // Get all workflow orders from internal and external assignments.
            $reviewOrders = TaskReview::where('task_id', $task->id)
                ->pluck('review_order');

            $routingOrders = TaskRouting::where('task_id', $task->id)
                ->pluck('review_order');

            $orders = $reviewOrders
                ->merge($routingOrders)
                ->filter(fn($order) => $order !== null)
                ->unique()
                ->sort()
                ->values();

            if ($orders->isEmpty()) {
                return [
                    'error' => 'No workflow reviewers or external destinations are configured.',
                ];
            }

            // Find the first unfinished workflow group.
            $activeOrder = null;

            foreach ($orders as $order) {

                $reviews = TaskReview::with('workflowStep')
                    ->where('task_id', $task->id)
                    ->where('review_order', $order)
                    ->get();

                $routings = TaskRouting::where('task_id', $task->id)
                    ->where('review_order', $order)
                    ->get();

                // Determine the completion rule for the internal review group.
                $completionRule = $reviews->first()
                    ?->workflowStep
                    ?->completion_rule ?? 'all';

                // Check internal review completion.
                if ($reviews->isEmpty()) {

                    $internalComplete = true;
                } elseif ($completionRule === 'any') {

                    $internalComplete = $reviews->contains(function ($review) {
                        return in_array($review->status, [
                            'Completed',
                            'Not Applicable',
                        ]);
                    });
                } else {

                    $internalComplete = $reviews->every(function ($review) {
                        return in_array($review->status, [
                            'Completed',
                            'Not Applicable',
                        ]);
                    });
                }

                // Check external routing completion.
                $externalComplete = $routings->every(function ($routing) {
                    return in_array($routing->status, [
                        'Completed',
                        'Not Applicable',
                    ]);
                });

                if (!$internalComplete || !$externalComplete) {
                    $activeOrder = $order;
                    break;
                }
            }

            if ($activeOrder === null) {
                return [
                    'error' => 'All workflow groups have already been completed.',
                ];
            }

            // Prevent forwarding the same group twice.
            $alreadyForwarded = TaskActivityLog::where('task_id', $task->id)
                ->where('action', 'Workflow Group Forwarded')
                ->get()
                ->contains(function ($log) use ($activeOrder) {
                    return (int) data_get($log->details, 'review_order')
                        === (int) $activeOrder;
                });

            if ($alreadyForwarded) {
                return [
                    'error' => 'This workflow group has already been forwarded. Wait for the group to complete before forwarding the next group.',
                ];
            }

            $reviews = TaskReview::with('reviewer')
                ->where('task_id', $task->id)
                ->where('review_order', $activeOrder)
                ->get();

            $routings = TaskRouting::with([
                'destinationDivision',
                'destinationOffice',
            ])
                ->where('task_id', $task->id)
                ->where('review_order', $activeOrder)
                ->get();

            // Identify internal reviewers who still need to act.
            $reviewers = $reviews
                ->filter(function ($review) {
                    return !in_array($review->status, [
                        'Completed',
                        'Not Applicable',
                    ]);
                })
                ->pluck('reviewer')
                ->filter()
                ->unique('id')
                ->values();

            // Count pending external destinations before dispatching.
            $dispatchedRoutingCount = $routings
                ->where('status', 'Pending')
                ->count();

            // Record the forwarding of external destinations.
            foreach ($routings as $routing) {

                if ($routing->status !== 'Pending') {
                    continue;
                }

                $previousStatus = $routing->status;

                $routing->update([
                    'status' => 'Dispatched',
                    'dispatched_by' => Auth::id(),
                    'dispatched_at' => now(),
                    'remarks' => $validated['remarks'],
                    'action_by' => Auth::id(),
                    'action_at' => now(),
                    'qr_token' => $routing->qr_token ?: bin2hex(random_bytes(32)),
                ]);

                TaskRoutingLog::create([
                    'task_routing_id' => $routing->id,
                    'user_id' => Auth::id(),
                    'action' => 'Document Dispatched',
                    'previous_status' => $previousStatus,
                    'new_status' => 'Dispatched',
                    'remarks' => $validated['remarks'],
                    'details' => [
                        'review_order' => $activeOrder,
                        'dispatched_by' => $senderName,
                        'dispatched_at' => now()->toDateTimeString(),
                    ],
                    'created_at' => now(),
                ]);
            }

            // Record the workflow forwarding action.
            TaskActivityLog::create([
                'task_id' => $task->id,
                'user_id' => Auth::id(),
                'action' => 'Workflow Group Forwarded',
                'details' => [
                    'review_order' => $activeOrder,
                    'forwarded_by' => $senderName,
                    'remarks' => $validated['remarks'],
                    'internal_reviewers' => $reviewers->pluck('id')->values()->all(),
                    'external_routings' => $routings->pluck('id')->values()->all(),
                    'forwarded_at' => now()->toDateTimeString(),
                ],
            ]);

            // Preserve the existing initial submission log.
            $alreadySubmitted = TaskActivityLog::where('task_id', $task->id)
                ->where('action', 'Submitted for Review')
                ->exists();

            if (!$alreadySubmitted) {

                TaskActivityLog::create([
                    'task_id' => $task->id,
                    'user_id' => Auth::id(),
                    'action' => 'Submitted for Review',
                    'details' => [
                        'submitted_by' => $senderName,
                        'submitted_at' => now()->toDateTimeString(),
                    ],
                ]);

                $forReviewStatus = TaskStatus::where('name', 'For Review')->first();

                if ($forReviewStatus) {
                    $task->update([
                        'status_id' => $forReviewStatus->id,
                    ]);
                }
            }

            return [
                'success' => true,
                'reviewers' => $reviewers,
                'routing_count' => $dispatchedRoutingCount,
                'review_order' => $activeOrder,
            ];
        });

        if (isset($result['error'])) {
            return back()->with('error', $result['error']);
        }

        // Notify internal reviewers after the database transaction.
        foreach ($result['reviewers'] as $reviewer) {
            $reviewer->notify(
                new TaskSubmittedForReview($task, $senderName)
            );
        }

        return back()->with(
            'success',
            'Document forwarded successfully. Internal reviewers have been notified and external destinations have been dispatched.'
        );
    }


    /**
     * Resolve users belonging to a workflow assignment target.
     */

    private function resolveReviewers(string $type, int $assignmentId)
    {
        $query = User::query();

        switch ($type) {

            case 'user':
                $query->whereKey($assignmentId);
                break;

            case 'role':
                $query->whereHas('roles', function ($roleQuery) use ($assignmentId) {
                    $roleQuery->where('roles.id', $assignmentId);
                });
                break;

            case 'office':
                $query->where('office_id', $assignmentId);
                break;

            case 'division':
                $query->where('division_id', $assignmentId);
                break;

            case 'position':
                $query->where('position_id', $assignmentId);
                break;

            case 'organizational_unit':
                $query->where('organizational_unit_id', $assignmentId);
                break;

            default:
                throw ValidationException::withMessages([
                    'workflow_template_id' =>
                    "Unsupported reviewer assignment type: {$type}.",
                ]);
        }

        return $query->orderBy('name')->get();
    }
    public function uploadAttachment(
        Request $request,
        Task $task,
        GoogleDriveService $googleDrive
    ) {
        // Only the task creator or assigned personnel can upload.
        $userId = Auth::id();

        $canUpload =
            (int) $task->created_by === (int) $userId ||
            TaskAssignment::where('task_id', $task->id)
            ->where('user_id', $userId)
            ->exists();

        abort_unless($canUpload, 403, 'You are not authorized to upload attachments.');

        // Validate the uploaded file.
        $request->validate([
            'attachment' => ['required', 'file', 'max:20480'],
        ]);

        $file = $request->file('attachment');

        // Upload directly to the PAP's General Documents folder.
        // Load the task's associated activity.
        $task->loadMissing('activity');

        // Upload to the activity folder if linked to an activity.
        // Otherwise, use the PAP's General Documents folder.
        if ($task->activity) {
            $driveFile = $googleDrive->uploadActivityAttachment(
                $file,
                $task->pap,
                $task->activity
            );
        } else {
            $driveFile = $googleDrive->uploadTaskAttachment(
                $file,
                $task->pap
            );
        }

        // Save attachment information in BRO360.
        TaskAttachment::create([
            'task_id' => $task->id,
            'uploaded_by' => $userId,
            'original_name' => $driveFile['name'],
            'file_path' => 'google-drive',
            'google_drive_file_id' => $driveFile['id'],
            'mime_type' => $driveFile['mime_type'],
            'file_size' => $driveFile['size'] ?? $file->getSize(),
        ]);

        // Record the upload in task history.
        TaskActivityLog::create([
            'task_id' => $task->id,
            'user_id' => $userId,
            'action' => 'Attachment Uploaded',
            'details' => [
                'file_name' => $driveFile['name'],
            ],
        ]);

        return back()->with('success', 'Attachment uploaded successfully.');
    }



    public function downloadAttachment(TaskAttachment $attachment, GoogleDriveService $googleDrive)
    {
        $task = $attachment->task;

        $userId = Auth::id();

        $hasAccess =
            (int) $task->created_by === (int) $userId ||
            TaskAssignment::where('task_id', $task->id)
            ->where('user_id', $userId)
            ->exists() ||
            TaskReview::where('task_id', $task->id)
            ->where('reviewer_id', $userId)
            ->exists();

        abort_unless($hasAccess, 403);

        abort_unless($attachment->google_drive_file_id, 404);

        $fileContents = $googleDrive->downloadFile(
            $attachment->google_drive_file_id
        );

        return response($fileContents)
            ->header('Content-Type', $attachment->mime_type ?? 'application/octet-stream')
            ->header(
                'Content-Disposition',
                'attachment; filename="' . addslashes($attachment->original_name) . '"'
            );
    }


    public function previewAttachment(
        TaskAttachment $attachment,
        GoogleDriveService $googleDrive
    ) {
        $task = $attachment->task;

        $userId = Auth::id();

        $hasAccess =
            (int) $task->created_by === (int) $userId ||
            TaskAssignment::where('task_id', $task->id)
            ->where('user_id', $userId)
            ->exists() ||
            TaskReview::where('task_id', $task->id)
            ->where('reviewer_id', $userId)
            ->exists();

        abort_unless($hasAccess, 403);

        abort_unless($attachment->google_drive_file_id, 404);

        $allowedTypes = [
            'application/pdf',
            'image/jpeg',
            'image/png',
        ];

        abort_unless(
            in_array($attachment->mime_type, $allowedTypes),
            415,
            'This file type cannot be previewed.'
        );

        $fileContents = $googleDrive->downloadFile(
            $attachment->google_drive_file_id
        );

        return response($fileContents, 200)
            ->header(
                'Content-Type',
                $attachment->mime_type
            )
            ->header(
                'Content-Disposition',
                'inline; filename="' .
                    addslashes($attachment->original_name) . '"'
            )
            ->header('X-Content-Type-Options', 'nosniff')
            ->header('Cache-Control', 'private, no-store');
    }


    public function updateReviewerStatus(
        Request $request,
        Task $task,
        TaskReview $review
    ) {
        // Only assigned personnel can manage reviewer statuses.
        $isAssignee = TaskAssignment::where('task_id', $task->id)
            ->where('user_id', Auth::id())
            ->exists();

        abort_unless($isAssignee, 403);

        // Ensure the review belongs to this task.
        abort_unless((int) $review->task_id === (int) $task->id, 404);

        $validated = $request->validate([
            'status' => [
                'required',
                Rule::in([
                    'Pending',
                    'Received',
                    'Completed',
                    'Returned',
                    'Not Applicable',
                ]),
            ],
            'remarks' => ['required', 'string', 'max:2000'],
        ]);

        $previousStatus = $review->status;
        $newStatus = $validated['status'];

        DB::transaction(function () use (
            $review,
            $task,
            $previousStatus,
            $newStatus,
            $validated
        ) {
            $review->status = $newStatus;
            $review->remarks = $validated['remarks'];

            if ($newStatus === 'Received') {
                $review->received_at = now();
            }

            if (in_array($newStatus, ['Completed', 'Not Applicable'])) {
                $review->reviewed_at = now();
            }

            if ($newStatus === 'Returned') {
                $review->returned_at = now();
            }

            $review->save();

            TaskActivityLog::create([
                'task_id' => $task->id,
                'user_id' => Auth::id(),
                'action' => 'Reviewer Status Updated',
                'details' => [
                    'reviewer' => $review->reviewer->name ?? 'Unknown',
                    'previous_status' => $previousStatus,
                    'new_status' => $newStatus,
                    'remarks' => $validated['remarks'],
                ],
            ]);
        });

        return back()->with('success', 'Reviewer status updated successfully.');
    }

    public function updateRoutingStatus(
        Request $request,
        Task $task,
        TaskRouting $routing
    ) {
        $isAssignee = TaskAssignment::where('task_id', $task->id)
            ->where('user_id', Auth::id())
            ->exists();

        abort_unless($isAssignee, 403);

        abort_unless((int) $routing->task_id === (int) $task->id, 404);

        $validated = $request->validate([
            'status' => [
                'required',
                Rule::in([
                    'Pending',
                    'Dispatched',
                    'Received',
                    'Completed',
                    'Returned',
                    'For Revision',
                    'On Hold',
                    'Cancelled',
                ]),
            ],
            'remarks' => ['required', 'string', 'max:2000'],
        ]);

        $previousStatus = $routing->status;
        $newStatus = $validated['status'];

        DB::transaction(function () use (
            $routing,
            $task,
            $previousStatus,
            $newStatus,
            $validated
        ) {
            $routing->status = $newStatus;
            $routing->remarks = $validated['remarks'];
            $routing->action_by = Auth::id();
            $routing->action_at = now();
            $routing->save();

            \App\Models\TaskRoutingLog::create([
                'task_routing_id' => $routing->id,
                'user_id' => Auth::id(),
                'action' => 'Routing Status Updated',
                'previous_status' => $previousStatus,
                'new_status' => $newStatus,
                'remarks' => $validated['remarks'],
                'details' => [
                    'updated_by' => Auth::user()->name,
                ],
            ]);

            TaskActivityLog::create([
                'task_id' => $task->id,
                'user_id' => Auth::id(),
                'action' => 'External Routing Status Updated',
                'details' => [
                    'routing_id' => $routing->id,
                    'previous_status' => $previousStatus,
                    'new_status' => $newStatus,
                    'remarks' => $validated['remarks'],
                ],
            ]);
        });

        return back()->with('success', 'External routing status updated successfully.');
    }
    public function myReviews()
    {
        $userId = Auth::id();

        $reviewRecords = TaskReview::with([
            'task.pap',
            'task.activity',
            'task.status',
            'task.reviews.workflowStep',
            'task.routings',
            'task.activityLogs',
        ])
            ->where('reviewer_id', $userId)
            ->whereHas('task.activityLogs', function ($query) {
                $query->where('action', 'Submitted for Review');
            })
            ->get();

        $tasks = $reviewRecords
            ->filter(function ($review) {

                $task = $review->task;

                // Do not show reviews while the document is being revised.
                if ($task->status?->name === 'For Revision') {
                    return false;
                }

                // Combine internal review and external routing orders.
                $workflowOrders = $task->reviews
                    ->pluck('review_order')
                    ->merge($task->routings->pluck('review_order'))
                    ->unique()
                    ->sort()
                    ->values();

                $activeOrder = null;

                // Identify the first unfinished workflow group.
                foreach ($workflowOrders as $order) {

                    $groupReviews = $task->reviews
                        ->where('review_order', $order);

                    $groupRoutings = $task->routings
                        ->where('review_order', $order);

                    // Determine internal review completion.
                    $internalComplete = true;

                    if ($groupReviews->isNotEmpty()) {

                        $workflowStep = $groupReviews->first()->workflowStep;

                        $completionRule = $workflowStep?->completion_rule ?? 'all';

                        $isFinalized = fn($item) => in_array(
                            $item->status,
                            ['Completed', 'Not Applicable']
                        );

                        if ($completionRule === 'any') {
                            $internalComplete = $groupReviews->contains($isFinalized);
                        } else {
                            $internalComplete = $groupReviews->every($isFinalized);
                        }
                    }

                    // Determine external routing completion.
                    $externalComplete = $groupRoutings->every(function ($routing) {
                        return in_array($routing->status, [
                            'Completed',
                            'Not Applicable',
                        ]);
                    });

                    if (!$internalComplete || !$externalComplete) {
                        $activeOrder = $order;
                        break;
                    }
                }

                if ($activeOrder === null) {
                    return false;
                }

                // The current group must already have been forwarded.
                $forwarded = $task->activityLogs->contains(function ($log) use ($activeOrder) {
                    return $log->action === 'Workflow Group Forwarded'
                        && (int) data_get($log->details, 'review_order')
                        === (int) $activeOrder;
                });

                if (!$forwarded) {
                    return false;
                }

                // Show only the current user's actionable review.
                return (int) $review->review_order === (int) $activeOrder
                    && !in_array($review->status, [
                        'Completed',
                        'Not Applicable',
                        'Returned',
                    ]);
            })
            ->map(fn($review) => $review->task)
            ->unique('id')
            ->values();

        return view('tasks.myreviews', compact('tasks'));
    }

    public function reviewerAction(Request $request, Task $task, TaskReview $review)
    {
        abort_unless(
            (int) $review->task_id === (int) $task->id,
            404
        );

        // Only the assigned reviewer can update their own review.
        abort_unless(
            (int) $review->reviewer_id === (int) Auth::id(),
            403,
            'You are not authorized to update this review.'
        );

        $validated = $request->validate([
            'status' => 'required|in:Received,Completed,Returned',
            'remarks' => 'required|string|max:2000',
        ]);

        // Verify that this reviewer's group has been forwarded.
        $forwarded = TaskActivityLog::where('task_id', $task->id)
            ->where('action', 'Workflow Group Forwarded')
            ->get()
            ->contains(function ($log) use ($review) {
                return (int) data_get($log->details, 'review_order')
                    === (int) $review->review_order;
            });

        if (!$forwarded) {
            return back()->with(
                'error',
                'Your reviewer group has not yet received the document.'
            );
        }

        if (in_array($review->status, ['Completed', 'Returned', 'Not Applicable'])) {
            return back()->with(
                'error',
                'This review has already been finalized.'
            );
        }

        $previousStatus = $review->status;
        $newStatus = $validated['status'];
        $reviewerName = Auth::user()->name;

        DB::transaction(function () use (
            $task,
            $review,
            $validated,
            $previousStatus,
            $newStatus
        ) {
            $review->status = $newStatus;
            $review->remarks = $validated['remarks'];

            if ($newStatus === 'Received') {
                $review->received_at = now();
            }

            if ($newStatus === 'Completed') {
                $review->reviewed_at = now();
            }

            if ($newStatus === 'Returned') {
                $review->returned_at = now();

                $revisionStatus = TaskStatus::where('name', 'For Revision')->first();

                if ($revisionStatus) {
                    $task->update([
                        'status_id' => $revisionStatus->id,
                    ]);
                }
            }

            $review->save();

            TaskActivityLog::create([
                'task_id' => $task->id,
                'user_id' => Auth::id(),
                'action' => $newStatus === 'Returned'
                    ? 'Document Returned for Revision'
                    : 'Reviewer Status Updated',
                'details' => [
                    'reviewer_id' => Auth::id(),
                    'review_order' => $review->review_order,
                    'previous_status' => $previousStatus,
                    'new_status' => $newStatus,
                    'remarks' => $validated['remarks'],
                    'updated_at' => now()->toDateTimeString(),
                ],
            ]);
        });

        // Check whether all internal reviews are finalized.
        $pendingReviews = TaskReview::where('task_id', $task->id)
            ->whereNotIn('status', ['Completed', 'Not Applicable'])
            ->exists();

        // Check whether all external routings are completed.
        $pendingRoutings = TaskRouting::where('task_id', $task->id)
            ->whereNotIn('status', ['Completed', 'Not Applicable'])
            ->exists();

        // Mark the task as Completed only when everything is finalized.
        if (!$pendingReviews && !$pendingRoutings) {

            $completedStatus = TaskStatus::where(
                'name',
                'Completed'
            )->first();

            if ($completedStatus) {
                $task->update([
                    'status_id' => $completedStatus->id,
                ]);

                TaskActivityLog::create([
                    'task_id' => $task->id,
                    'user_id' => Auth::id(),
                    'action' => 'Task Completed',
                    'details' => [
                        'message' => 'All internal reviews and external routings have been completed.',
                        'completed_at' => now()->toDateTimeString(),
                    ],
                ]);
            }
        }

        // Notify the primary assignee when the document is returned.
        if ($newStatus === 'Returned') {
            $assignee = $task->assignee;

            if ($assignee) {
                $assignee->notify(
                    new \App\Notifications\TaskReturnedToAssignee(
                        $task,
                        $reviewerName,
                        $validated['remarks']
                    )
                );
            }

            return back()->with(
                'success',
                'The document has been returned to the assignee for revision. The assignee has been notified.'
            );
        }

        return back()->with(
            'success',
            'Your review status has been updated successfully.'
        );
    }

    public function resubmitForReview(Request $request, Task $task)
    {
        // Only assigned personnel may resubmit.
        $isAssigned = TaskAssignment::where('task_id', $task->id)
            ->where('user_id', Auth::id())
            ->exists();

        abort_unless(
            $isAssigned,
            403,
            'You are not authorized to resubmit this task.'
        );

        $validated = $request->validate([
            'remarks' => 'required|string|max:2000',
        ]);

        // Find the returned reviewer group.
        $returnedReviews = TaskReview::with('reviewer')
            ->where('task_id', $task->id)
            ->where('status', 'Returned')
            ->orderBy('review_order')
            ->get();

        if ($returnedReviews->isEmpty()) {
            return back()->with(
                'error',
                'There are no returned reviews awaiting resubmission.'
            );
        }

        // Only resubmit to the earliest returned workflow group.
        $returnedOrder = $returnedReviews->first()->review_order;

        $returnedReviews = $returnedReviews
            ->where('review_order', $returnedOrder)
            ->values();

        // Require a new attachment uploaded after the document was returned.
        $latestReturn = $returnedReviews
            ->max('returned_at');

        $newAttachmentExists = \App\Models\TaskAttachment::where('task_id', $task->id)
            ->where('created_at', '>', $latestReturn)
            ->exists();

        if (!$newAttachmentExists) {
            return back()->with(
                'error',
                'Please upload the revised document before resubmitting it for review.'
            );
        }

        $reviewerIds = $returnedReviews
            ->pluck('reviewer_id')
            ->unique()
            ->values();

        DB::transaction(function () use (
            $task,
            $returnedReviews,
            $validated,
            $returnedOrder
        ) {
            foreach ($returnedReviews as $review) {
                $review->update([
                    'status' => 'Pending',
                    'reviewed_at' => null,
                    'returned_at' => null,
                ]);
            }

            // Restore the task status.
            $forReviewStatus = TaskStatus::where('name', 'For Review')->first();

            if ($forReviewStatus) {
                $task->update([
                    'status_id' => $forReviewStatus->id,
                ]);
            }

            TaskActivityLog::create([
                'task_id' => $task->id,
                'user_id' => Auth::id(),
                'action' => 'Document Resubmitted for Review',
                'details' => [
                    'review_order' => $returnedOrder,
                    'reviewer_ids' => $returnedReviews->pluck('reviewer_id')->values()->all(),
                    'resubmitted_by' => Auth::user()->name,
                    'remarks' => $validated['remarks'],
                    'resubmitted_at' => now()->toDateTimeString(),
                ],
            ]);
        });

        // Notify the reviewer or reviewers who returned the document.
        $reviewers = \App\Models\User::whereIn('id', $reviewerIds)->get();



        foreach ($reviewers as $reviewer) {
            $reviewer->notify(
                new TaskSubmittedForReview(
                    $task,
                    Auth::user()->name
                )
            );
        }

        return back()->with(
            'success',
            'The revised document has been resubmitted to the returning reviewer group.'
        );
    }

    public function liaisonView(string $token)
    {
        $task = Task::with([
            'pap',
            'routings.destinationDivision',
            'routings.destinationOffice',
            'routings.logs.user',
        ])
            ->where('qr_review_token', $token)
            ->firstOrFail();

        return view('tasks.liaison', compact('task'));
    }

    public function liaisonUpdateRouting(
        Request $request,
        string $token,
        TaskRouting $routing,
        GoogleDriveService $googleDrive
    ) {
        // Find the task using its secure QR token.
        $task = Task::with('pap')
            ->where('qr_review_token', $token)
            ->firstOrFail();

        // Ensure this routing record belongs to the task.
        abort_unless($routing->task_id === $task->id, 404);

        // Validate submitted information.
        $validated = $request->validate([
            'status' => [
                'required',
                'in:Pending,Dispatched,Received,Completed,Returned,For Revision,On Hold,Cancelled',
            ],
            'photo' => [
                'nullable',
                'image',
                'max:10240',
            ],
            'remarks' => [
                'nullable',
                'string',
                'max:2000',
            ],
            'receiving_name' => [
                'required',
                'string',
                'max:255',
            ],
        ]);

        $previousStatus = $routing->status;

        // Upload photo proof to Google Drive, if provided.
        $uploadedPhoto = null;

        if ($request->hasFile('photo')) {

            // Load the task's associated activity.
            $task->loadMissing('activity');

            // Upload proof to the activity folder or General Documents.
            $uploadedPhoto = $googleDrive->uploadLiaisonProof(
                $request->file('photo'),
                $task->pap,
                $task->activity
            );
        }

        // Update routing record.
        $routing->status = $validated['status'];
        $routing->remarks = $validated['remarks'] ?? null;
        $routing->receiving_remarks = $validated['remarks'] ?? null;
        $routing->action_by = null;
        $routing->action_at = now();
        $routing->receiving_name = $validated['receiving_name'];

        if ($validated['status'] === 'Dispatched' && !$routing->dispatched_at) {
            $routing->dispatched_at = now();
        }

        if ($validated['status'] === 'Received' && !$routing->received_at) {
            $routing->received_at = now();
        }

        if ($uploadedPhoto) {
            $routing->receiving_photo_drive_id = $uploadedPhoto['id'];
            $routing->receiving_photo_name = $uploadedPhoto['name'];
        }



        $routing->save();

        // Record the status change in routing history.
        TaskRoutingLog::create([
            'task_routing_id' => $routing->id,
            'user_id' => null,
            'action' => 'Liaison Routing Status Updated',
            'previous_status' => $previousStatus,
            'new_status' => $routing->status,
            'remarks' => $validated['remarks'] ?? null,
            'details' => [
                'photo_uploaded' => $uploadedPhoto !== null,
                'photo_drive_id' => $uploadedPhoto['id'] ?? null,
                'updated_at' => now()->toDateTimeString(),
            ],
            'created_at' => now(),
        ]);

        // Check whether all internal reviews are finalized.
        $pendingReviews = TaskReview::where('task_id', $task->id)
            ->whereNotIn('status', ['Completed', 'Not Applicable'])
            ->exists();

        // Check whether all external routings are finalized.
        $pendingRoutings = TaskRouting::where('task_id', $task->id)
            ->whereNotIn('status', ['Completed', 'Not Applicable'])
            ->exists();

        // Complete the task if all workflow stages are finalized.
        if (!$pendingReviews && !$pendingRoutings) {

            $completedStatus = TaskStatus::where('name', 'Completed')->first();

            if ($completedStatus && $task->status_id != $completedStatus->id) {

                $task->update([
                    'status_id' => $completedStatus->id,
                ]);

                TaskActivityLog::create([
                    'task_id' => $task->id,
                    'user_id' => null,
                    'action' => 'Task Completed',
                    'details' => [
                        'message' => 'All internal reviews and external routings have been completed.',
                        'completed_at' => now()->toDateTimeString(),
                    ],
                ]);
            }
        }

        return redirect()
            ->route('liaison.task.show', $token)
            ->with('success', 'Routing information successfully updated.');
    }

    public function startTask(Task $task)
    {
        // Only assigned personnel can start the task.
        abort_unless(
            $task->assignments()
                ->where('user_id', Auth::id())
                ->exists(),
            403,
            'You are not authorized to start this task.'
        );

        // Only Pending tasks can be started.
        if ($task->status?->name !== 'Pending') {
            return back()->with('error', 'This task cannot be started.');
        }

        $inProgressStatus = TaskStatus::where('name', 'In Progress')->firstOrFail();

        $task->update([
            'status_id' => $inProgressStatus->id,
        ]);

        return back()->with('success', 'Task successfully started.');
    }

    public function qrCode(Task $task)
    {
        abort_if(
            empty($task->qr_review_token),
            404,
            'QR code token not found for this task.'
        );

        $url = route('liaison.task.show', [
            'token' => $task->qr_review_token,
        ]);

        $qrCode = new QrCode(
            data: $url,
            encoding: new Encoding('UTF-8'),
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: 300,
            margin: 10,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,
            foregroundColor: new Color(21, 59, 42),
            backgroundColor: new Color(255, 255, 255)
        );

        $writer = new PngWriter();
        $result = $writer->write($qrCode);

        $png = $result->getString();



        return response($result->getString(), 200, [
            'Content-Type' => 'image/png',
            'Content-Disposition' => 'inline; filename="task-qr-code.png"',
            'Content-Length' => strlen($result->getString()),
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function reassign(Request $request, Task $task)
    {
        $validated = $request->validate([
            'new_assignee_id' => [
                'required',
                'integer',
                'exists:users,id',
            ],
        ]);

        // Only the task creator or current primary assignee can reassign.
        abort_unless(
            (int) Auth::id() === (int) $task->created_by ||
                (int) Auth::id() === (int) $task->assigned_to,
            403
        );

        $previousAssignee = User::findOrFail($task->assigned_to);
        $newAssignee = User::findOrFail($validated['new_assignee_id']);

        if ($previousAssignee->id === $newAssignee->id) {
            return back()->withErrors([
                'new_assignee_id' => 'This user is already the primary assignee.',
            ]);
        }

        DB::transaction(function () use (
            $task,
            $previousAssignee,
            $newAssignee
        ) {
            // Change the previous primary assignee to a regular assignee.
            TaskAssignment::where('task_id', $task->id)
                ->where('user_id', $previousAssignee->id)
                ->update([
                    'assignment_role' => 'Assignee',
                ]);

            // Check whether the new primary assignee is already assigned.
            $existingAssignment = TaskAssignment::where('task_id', $task->id)
                ->where('user_id', $newAssignee->id)
                ->first();

            if ($existingAssignment) {
                $existingAssignment->update([
                    'assignment_role' => 'Primary',
                    'assigned_by' => Auth::id(),
                ]);
            } else {
                TaskAssignment::create([
                    'task_id' => $task->id,
                    'user_id' => $newAssignee->id,
                    'assignment_role' => 'Primary',
                    'assigned_by' => Auth::id(),
                ]);
            }

            // Update the task's primary assignee.
            $task->update([
                'assigned_to' => $newAssignee->id,
            ]);

            // Record the reassignment.
            TaskActivityLog::create([
                'task_id' => $task->id,
                'user_id' => Auth::id(),
                'action' => 'Reassigned',
                'details' => [
                    'previous_assignee_id' => $previousAssignee->id,
                    'previous_assignee_name' => $previousAssignee->name,
                    'new_assignee_id' => $newAssignee->id,
                    'new_assignee_name' => $newAssignee->name,
                ],
            ]);

            // Notify the new primary assignee after the transaction commits.
            DB::afterCommit(function () use (
                $task,
                $previousAssignee,
                $newAssignee
            ) {
                $newAssignee->notify(
                    new TaskReassigned(
                        $task,
                        $previousAssignee->name,
                        $newAssignee->name,
                        Auth::user()?->name ?? 'BRO360'
                    )
                );
            });
        });

        return back()->with(
            'success',
            'Task successfully reassigned.'
        );
    }

    /**
     * Display the task editing form.
     */
    public function edit(Task $task)
    {
        // Only the task creator can edit the task.
        abort_unless(
            (int) $task->created_by === (int) Auth::id(),
            403,
            'You are not authorized to edit this task.'
        );

        $task->load([
            'assignments',
            'taskType',
            'workflowTemplate',
        ]);

        $paps = Pap::with('activities')
            ->orderBy('name')
            ->get();

        $users = User::orderBy('name')->get();

        return view('tasks.edit', compact(
            'task',
            'paps',
            'users'
        ));
    }

    /**
     * Update task information without resetting its workflow.
     */
    public function update(Request $request, Task $task)
    {
        // Only the task creator can edit the task.
        abort_unless(
            (int) $task->created_by === (int) Auth::id(),
            403,
            'You are not authorized to edit this task.'
        );

        $validated = $request->validate([
            'pap_id' => [
                'required',
                'exists:paps,id',
            ],

            'pap_activity_id' => [
                'nullable',
                'exists:pap_activities,id',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'priority' => [
                'required',
                Rule::in(['Low', 'Normal', 'High', 'Urgent']),
            ],

            'assigned_users' => [
                'required',
                'array',
                'min:1',
            ],

            'assigned_users.*' => [
                'required',
                'integer',
                'distinct',
                'exists:users,id',
            ],

            'primary_assignee_id' => [
                'required',
                'integer',
                Rule::in($request->input('assigned_users', [])),
            ],

            'start_date' => [
                'nullable',
                'date',
            ],

            'due_date' => [
                'nullable',
                'date',
                'after_or_equal:start_date',
            ],

            'task_type_id' => ['required', 'exists:task_types,id'],
        ]);

        $taskType = TaskType::with('workflowTemplate')
            ->where('is_active', true)
            ->findOrFail($validated['task_type_id']);

        $workflowTemplate = $taskType->workflowTemplate;

        if (
            !$workflowTemplate ||
            !$workflowTemplate->is_active ||
            $workflowTemplate->process_type !== 'task'
        ) {
            throw ValidationException::withMessages([
                'task_type_id' => 'The selected task title does not have a valid active workflow.',
            ]);
        }

        // Verify that the selected activity belongs to the selected PAP.
        if (!empty($validated['pap_activity_id'])) {
            $activityBelongsToPap = DB::table('pap_activities')
                ->where('id', $validated['pap_activity_id'])
                ->where('pap_id', $validated['pap_id'])
                ->exists();

            if (!$activityBelongsToPap) {
                throw ValidationException::withMessages([
                    'pap_activity_id' =>
                    'The selected activity does not belong to the selected PAP.',
                ]);
            }
        }

        DB::transaction(function () use ($task, $validated, $taskType, $workflowTemplate) {

            // Update basic task information.
            $task->update([
                'pap_id' => $validated['pap_id'],
                'pap_activity_id' => $validated['pap_activity_id'] ?? null,
                'description' => $validated['description'] ?? null,
                'priority' => $validated['priority'],
                'assigned_to' => $validated['primary_assignee_id'],
                'start_date' => $validated['start_date'] ?? null,
                'due_date' => $validated['due_date'] ?? null,
                'task_type_id' => $taskType->id,
                'title' => $taskType->title,
                'workflow_template_id' => $workflowTemplate->id,
            ]);

            // Synchronize assigned personnel.
            TaskAssignment::where('task_id', $task->id)
                ->whereNotIn('user_id', $validated['assigned_users'])
                ->delete();

            foreach ($validated['assigned_users'] as $userId) {

                TaskAssignment::updateOrCreate(
                    [
                        'task_id' => $task->id,
                        'user_id' => $userId,
                    ],
                    [
                        'assignment_role' =>
                        (int) $userId === (int) $validated['primary_assignee_id']
                            ? 'Primary'
                            : 'Assignee',
                        'assigned_by' => Auth::id(),
                    ]
                );
            }

            // Record the update in task history.
            TaskActivityLog::create([
                'task_id' => $task->id,
                'user_id' => Auth::id(),
                'action' => 'Task Updated',
                'details' => [
                    'updated_by' => Auth::user()->name,
                    'updated_at' => now()->toDateTimeString(),
                ],
            ]);
        });

        return redirect()
            ->route('tasks.show', $task->id)
            ->with('success', 'Task information updated successfully.');
    }

    public function destroy(Task $task)
    {
        // Only the task creator can delete the task
        if ((int) $task->created_by !== (int) Auth::id()) {
            abort(403, 'You are not authorized to delete this task.');
        }

        try {
            DB::transaction(function () use ($task) {

                // Delete task assignments, reviews, comments, and attachments
                $task->assignments()->delete();
                $task->reviews()->delete();
                $task->comments()->delete();
                $task->attachments()->delete();
                $task->activityLogs()->delete();

                // Delete routing logs before routing records
                foreach ($task->routings as $routing) {
                    $routing->logs()->delete();
                    $routing->delete();
                }

                // Permanently delete documents and their versions
                foreach ($task->documents()->withTrashed()->get() as $document) {

                    // Clear document references to versions first
                    $document->current_version_id = null;
                    $document->approved_version_id = null;
                    $document->save();

                    $document->versions()->delete();
                    $document->forceDelete();
                }

                // Permanently delete the task itself
                $task->forceDelete();
            });

            return redirect()
                ->route('tasks.index')
                ->with('success', 'Task permanently deleted.');
        } catch (\Exception $e) {

            report($e);

            return redirect()
                ->route('tasks.index')
                ->with('error', 'Unable to permanently delete the task.');
        }
    }
}
