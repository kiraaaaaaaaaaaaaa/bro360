<?php

namespace App\Http\Controllers;

use App\Models\Pap;
use App\Models\PapActivity;
use App\Models\Task;
use App\Models\TaskReview;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Division;


class DashboardController extends Controller
{
    public function index()
    {
        // Staff Dashboard
        $user = Auth::user();

        if ($user && $user->roles->contains('name', 'Division Head')) {
            return $this->divisionHeadDashboard();
        }

        if ($user && $user->roles->contains('name', 'Sector Head')) {
            return $this->sectorHeadDashboard();
        }

        if ($user) {
            $isStaff = $user->roles->contains('name', 'Staff');
            $isReviewer = $user->roles->contains('name', 'Reviewer');

            if ($isStaff && $isReviewer) {
                return $this->reviewerDashboard();
            }

            if ($isStaff) {
                return $this->staffDashboard();
            }
        }


        // Office Overview
        $totalPaps = Pap::count();

        $totalActivities = PapActivity::count();

        $totalTasks = Task::count();

        $activeTasks = Task::whereHas('status', function ($query) {
            $query->where('name', 'In Progress');
        })->count();

        $pendingTasks = Task::whereHas('status', function ($query) {
            $query->where('name', 'Pending');
        })->count();

        $completedTasks = Task::whereHas('status', function ($query) {
            $query->where('name', 'Completed');
        })->count();

        $pendingReviews = TaskReview::where('status', 'Pending')->count();


        // Upcoming PAP Activities
        $upcomingActivities = PapActivity::with('pap')
            ->whereNotNull('start_date')
            ->whereDate('end_date', '>=', today())
            ->orderBy('start_date')
            ->limit(10)
            ->get();


        // Upcoming Tasks
        $upcomingTasks = Task::with(['pap', 'activity'])
            ->where(function ($query) {
                $query->whereDate('due_date', '>=', today())
                    ->orWhere(function ($query) {
                        $query->whereNull('due_date')
                            ->whereDate('start_date', '>=', today());
                    });
            })
            ->whereHas('status', function ($query) {
                $query->whereNotIn('name', ['Completed', 'Cancelled']);
            })
            ->orderByRaw('COALESCE(due_date, start_date) ASC')
            ->limit(10)
            ->get();


        // PAP Implementation Overview
        $paps = Pap::with('activities')
            ->latest()
            ->get();

        $papImplementation = $paps->map(function ($pap) {

            $activityProgress = $pap->activities->map(function ($activity) {
                return $activity->progress;
            });

            $progress = $activityProgress->isEmpty()
                ? 0
                : round($activityProgress->avg(), 2);

            return [
                'id' => $pap->id,
                'code' => $pap->pap_code,
                'name' => $pap->name,
                'progress' => $progress,
                'activity_count' => $pap->activities->count(),
            ];
        });

        $overallImplementation = $papImplementation->isEmpty()
            ? 0
            : round($papImplementation->avg('progress'), 2);


        // Task Overview
        $activities = PapActivity::with([
            'pap',
            'tasks.status',
            'tasks.reviews.reviewer',
            'tasks.routings.destinationDivision',
            'tasks.routings.destinationOffice',
        ])
            ->latest()
            ->get();

        $taskOverview = $activities->map(function ($activity) {

            // Latest task matching each category
            $categories = [
                'concept_note' => 'concept note',
                'apf' => 'apf',
                'memo' => 'memo',
                'par' => 'par',
            ];

            $categoryTasks = [];

            foreach ($categories as $key => $keyword) {

                $matchedTask = $activity->tasks
                    ->filter(function ($task) use ($keyword) {
                        return stripos($task->title, $keyword) !== false;
                    })
                    ->sortByDesc('created_at')
                    ->first();

                $categoryTasks[$key] = $matchedTask ? [
                    'id' => $matchedTask->id,
                    'title' => $matchedTask->title,
                    'status' => $matchedTask->status?->name ?? 'Unknown',
                    'current_stage' => $this->getCurrentTaskStage($matchedTask),
                ] : null;
            }

            return [
                'activity_id' => $activity->id,
                'activity_title' => $activity->title,
                'pap_name' => $activity->pap?->name ?? 'No PAP assigned',
                'categories' => $categoryTasks,
            ];
        });


        return view('dashboard', compact(
            'totalPaps',
            'totalActivities',
            'totalTasks',
            'activeTasks',
            'pendingTasks',
            'completedTasks',
            'pendingReviews',
            'upcomingActivities',
            'upcomingTasks',
            'papImplementation',
            'overallImplementation',
            'taskOverview'
        ));
    }


    private function reviewerDashboard()
    {
        $userId = Auth::id();

        // Staff task assignments
        $myTasks = Task::where(function ($query) use ($userId) {
            $query->where('assigned_to', $userId)
                ->orWhereHas('assignments', function ($assignmentQuery) use ($userId) {
                    $assignmentQuery->where('user_id', $userId);
                });
        });

        $taskCounts = [
            'pendingTasks' => (clone $myTasks)->whereHas(
                'status',
                fn($q) => $q->where('name', 'Pending')
            )->count(),

            'activeTasks' => (clone $myTasks)->whereHas(
                'status',
                fn($q) => $q->where('name', 'In Progress')
            )->count(),

            'forReviewTasks' => (clone $myTasks)->whereHas(
                'status',
                fn($q) => $q->where('name', 'For Review')
            )->count(),

            'completedTasks' => (clone $myTasks)->whereHas(
                'status',
                fn($q) => $q->where('name', 'Completed')
            )->count(),

            'revisionTasks' => (clone $myTasks)->whereHas(
                'status',
                fn($q) => $q->where('name', 'For Revision')
            )->count(),

            'onHoldTasks' => (clone $myTasks)->whereHas(
                'status',
                fn($q) => $q->where('name', 'On Hold')
            )->count(),
        ];

        $myTaskList = (clone $myTasks)
            ->with(['pap', 'activity', 'status'])
            ->latest()
            ->limit(10)
            ->get();

        // My review records
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

        // Identify actionable reviews using the existing sequential workflow rules
        $myPendingReviews = $reviewRecords
            ->filter(function ($review) {

                $task = $review->task;

                if (!$task || $task->status?->name === 'For Revision') {
                    return false;
                }

                $workflowOrders = $task->reviews
                    ->pluck('review_order')
                    ->merge($task->routings->pluck('review_order'))
                    ->unique()
                    ->sort()
                    ->values();

                $activeOrder = null;

                foreach ($workflowOrders as $order) {

                    $groupReviews = $task->reviews
                        ->where('review_order', $order);

                    $groupRoutings = $task->routings
                        ->where('review_order', $order);

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

                $forwarded = $task->activityLogs->contains(function ($log) use ($activeOrder) {
                    return $log->action === 'Workflow Group Forwarded'
                        && (int) data_get($log->details, 'review_order')
                        === (int) $activeOrder;
                });

                if (!$forwarded) {
                    return false;
                }

                return (int) $review->review_order === (int) $activeOrder
                    && !in_array($review->status, [
                        'Completed',
                        'Not Applicable',
                        'Returned',
                    ]);
            })
            ->unique('task_id')
            ->values();

        // Review summary
        $myReviewCounts = [
            'pendingReviews' => TaskReview::where('reviewer_id', $userId)
                ->where('status', 'Pending')->count(),

            'receivedReviews' => TaskReview::where('reviewer_id', $userId)
                ->where('status', 'Received')->count(),

            'completedReviews' => TaskReview::where('reviewer_id', $userId)
                ->where('status', 'Completed')->count(),

            'returnedReviews' => TaskReview::where('reviewer_id', $userId)
                ->where('status', 'Returned')->count(),
        ];

        // Review history
        $reviewHistory = TaskReview::with([
            'task.pap',
            'task.activity',
            'task.status',
        ])
            ->where('reviewer_id', $userId)
            ->whereIn('status', ['Completed', 'Returned'])
            ->latest('updated_at')
            ->limit(10)
            ->get();

        // Upcoming activities relevant to the user
        $upcomingActivities = PapActivity::with('pap')
            ->whereNotNull('start_date')
            ->whereDate('end_date', '>=', today())
            ->where(function ($query) use ($userId) {

                $query->where('focal_user_id', $userId)

                    ->orWhereHas('tasks', function ($taskQuery) use ($userId) {

                        $taskQuery->where(function ($assignmentQuery) use ($userId) {
                            $assignmentQuery->where('assigned_to', $userId)
                                ->orWhereHas('assignments', function ($q) use ($userId) {
                                    $q->where('user_id', $userId);
                                });
                        });

                        $taskQuery->orWhereHas('reviews', function ($reviewQuery) use ($userId) {
                            $reviewQuery->where('reviewer_id', $userId);
                        });
                    });
            })
            ->orderBy('start_date')
            ->limit(10)
            ->get();

        return view('dashboards.reviewer', array_merge(
            $taskCounts,
            $myReviewCounts,
            compact(
                'myTaskList',
                'myPendingReviews',
                'reviewHistory',
                'upcomingActivities'
            )
        ));
    }


    private function staffDashboard()
    {
        $userId = Auth::id();

        // Base query: tasks assigned directly or through task_assignments
        $myTasks = Task::where(function ($query) use ($userId) {
            $query->where('assigned_to', $userId)
                ->orWhereHas('assignments', function ($assignmentQuery) use ($userId) {
                    $assignmentQuery->where('user_id', $userId);
                });
        });

        // Task counts
        $pendingTasks = (clone $myTasks)
            ->whereHas('status', fn($query) => $query->where('name', 'Pending'))
            ->count();

        $activeTasks = (clone $myTasks)
            ->whereHas('status', fn($query) => $query->where('name', 'In Progress'))
            ->count();

        $forReviewTasks = (clone $myTasks)
            ->whereHas('status', fn($query) => $query->where('name', 'For Review'))
            ->count();

        $completedTasks = (clone $myTasks)
            ->whereHas('status', fn($query) => $query->where('name', 'Completed'))
            ->count();

        $revisionTasks = (clone $myTasks)
            ->whereHas('status', fn($query) => $query->where('name', 'For Revision'))
            ->count();

        $onHoldTasks = (clone $myTasks)
            ->whereHas('status', fn($query) => $query->where('name', 'On Hold'))
            ->count();

        // Upcoming deadlines
        $upcomingTasks = (clone $myTasks)
            ->with(['pap', 'activity', 'status'])
            ->whereNotNull('due_date')
            ->whereDate('due_date', '>=', today())
            ->whereHas('status', function ($query) {
                $query->whereNotIn('name', ['Completed', 'Cancelled']);
            })
            ->orderBy('due_date')
            ->limit(10)
            ->get();

        // My Pending Actions
        $pendingActionTasks = (clone $myTasks)
            ->with(['pap', 'activity', 'status'])
            ->whereHas('status', function ($query) {
                $query->whereIn('name', ['Pending', 'For Revision']);
            })
            ->orderByRaw('CASE WHEN due_date IS NULL THEN 1 ELSE 0 END')
            ->orderBy('due_date')
            ->limit(5)
            ->get();

        // Staff task list
        $myTaskList = (clone $myTasks)
            ->with(['pap', 'activity', 'status', 'reviews'])
            ->latest()
            ->limit(10)
            ->get();

        // Upcoming activities relevant to this staff member
        $upcomingActivities = PapActivity::with('pap')
            ->whereNotNull('start_date')
            ->whereDate('end_date', '>=', today())
            ->where(function ($query) use ($userId) {
                $query->where('focal_user_id', $userId)
                    ->orWhereHas('tasks', function ($taskQuery) use ($userId) {
                        $taskQuery->where(function ($assignmentQuery) use ($userId) {
                            $assignmentQuery->where('assigned_to', $userId)
                                ->orWhereHas('assignments', function ($query) use ($userId) {
                                    $query->where('user_id', $userId);
                                });
                        });
                    });
            })
            ->orderBy('start_date')
            ->limit(10)
            ->get();

        return view('dashboards.staff', compact(
            'pendingTasks',
            'activeTasks',
            'forReviewTasks',
            'completedTasks',
            'revisionTasks',
            'onHoldTasks',
            'upcomingTasks',
            'pendingActionTasks',
            'myTaskList',
            'upcomingActivities'
        ));
    }


    private function divisionHeadDashboard()
    {
        $user = Auth::user();
        $divisionId = $user->division_id;

        // Division PAPs
        $paps = Pap::with('activities')
            ->where('division_id', $divisionId)
            ->latest()
            ->get();

        $totalPaps = $paps->count();

        $totalActivities = PapActivity::whereHas('pap', function ($query) use ($divisionId) {
            $query->where('division_id', $divisionId);
        })->count();

        // Base query: tasks belonging to this division
        $divisionTasks = Task::whereHas('pap', function ($query) use ($divisionId) {
            $query->where('division_id', $divisionId);
        });

        // Division task summary
        $totalTasks = (clone $divisionTasks)->count();

        $pendingTasks = (clone $divisionTasks)
            ->whereHas('status', fn($q) => $q->where('name', 'Pending'))
            ->count();

        $activeTasks = (clone $divisionTasks)
            ->whereHas('status', fn($q) => $q->where('name', 'In Progress'))
            ->count();

        $forReviewTasks = (clone $divisionTasks)
            ->whereHas('status', fn($q) => $q->where('name', 'For Review'))
            ->count();

        $completedTasks = (clone $divisionTasks)
            ->whereHas('status', fn($q) => $q->where('name', 'Completed'))
            ->count();

        $revisionTasks = (clone $divisionTasks)
            ->whereHas('status', fn($q) => $q->where('name', 'For Revision'))
            ->count();

        $onHoldTasks = (clone $divisionTasks)
            ->whereHas('status', fn($q) => $q->where('name', 'On Hold'))
            ->count();

        // PAP implementation
        $papImplementation = $paps->map(function ($pap) {

            $progress = $pap->activities->isEmpty()
                ? 0
                : round($pap->activities->avg('progress'), 2);

            return [
                'id' => $pap->id,
                'code' => $pap->pap_code,
                'name' => $pap->name,
                'progress' => $progress,
                'activity_count' => $pap->activities->count(),
            ];
        });

        $overallImplementation = $papImplementation->isEmpty()
            ? 0
            : round($papImplementation->avg('progress'), 2);

        // Current Reviews
        $reviewTasks = (clone $divisionTasks)
            ->with([
                'pap',
                'activity',
                'status',
                'reviews.reviewer',
                'reviews.workflowStep',
                'routings.destinationDivision',
                'routings.destinationOffice',
                'activityLogs',
            ])
            ->where(function ($query) {
                $query->whereHas('reviews')
                    ->orWhereHas('routings');
            })
            ->get();

        $currentReviews = $reviewTasks->map(function ($task) {

            if ($task->status?->name === 'For Revision') {
                return null;
            }

            $workflowOrders = $task->reviews
                ->pluck('review_order')
                ->merge($task->routings->pluck('review_order'))
                ->unique()
                ->sort()
                ->values();

            $activeOrder = null;

            foreach ($workflowOrders as $order) {

                $groupReviews = $task->reviews->where('review_order', $order);
                $groupRoutings = $task->routings->where('review_order', $order);

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
                return null;
            }

            // Only show the current stage if it has been forwarded
            $forwarded = $task->activityLogs->contains(function ($log) use ($activeOrder) {
                return $log->action === 'Workflow Group Forwarded'
                    && (int) data_get($log->details, 'review_order') === (int) $activeOrder;
            });

            if (!$forwarded) {
                return null;
            }

            $groupReviews = $task->reviews
                ->where('review_order', $activeOrder);

            $groupRoutings = $task->routings
                ->where('review_order', $activeOrder);

            $reviewers = $groupReviews
                ->filter(fn($review) => !in_array($review->status, [
                    'Completed',
                    'Not Applicable',
                    'Returned',
                ]))
                ->map(fn($review) => [
                    'name' => $review->reviewer?->name ?? 'Unassigned reviewer',
                    'status' => $review->status,
                ])
                ->values();

            $destinations = $groupRoutings
                ->filter(fn($routing) => !in_array($routing->status, [
                    'Completed',
                    'Not Applicable',
                    'Cancelled',
                ]))
                ->map(function ($routing) {

                    $destination = null;

                    if ($routing->destination_type === 'division') {
                        $destination = $routing->destinationDivision?->name;
                    } elseif ($routing->destination_type === 'office') {
                        $destination = $routing->destinationOffice?->name;
                    }

                    return [
                        'name' => $destination
                            ?? $routing->receiving_office
                            ?? 'Unassigned destination',
                        'status' => $routing->status,
                    ];
                })
                ->values();

            return [
                'task' => $task,
                'review_order' => $activeOrder,
                'reviewers' => $reviewers,
                'destinations' => $destinations,
            ];
        })
            ->filter()
            ->values();

        // Division task monitoring
        $divisionTaskList = (clone $divisionTasks)
            ->with(['pap', 'activity', 'status'])
            ->latest()
            ->limit(15)
            ->get();

        // Division staff
        $divisionStaff = User::where('division_id', $divisionId)
            ->whereHas('roles', function ($query) {
                $query->where('name', 'Staff');
            })
            ->get()
            ->map(function ($staff) {

                $staffTasks = Task::where(function ($query) use ($staff) {
                    $query->where('assigned_to', $staff->id)
                        ->orWhereHas('assignments', function ($assignmentQuery) use ($staff) {
                            $assignmentQuery->where('user_id', $staff->id);
                        });
                })
                    ->whereHas('pap', function ($query) use ($staff) {
                        $query->where('division_id', $staff->division_id);
                    });

                return [
                    'name' => $staff->name,
                    'total_tasks' => (clone $staffTasks)->count(),
                    'completed_tasks' => (clone $staffTasks)
                        ->whereHas('status', fn($q) => $q->where('name', 'Completed'))
                        ->count(),
                    'active_tasks' => (clone $staffTasks)
                        ->whereHas('status', fn($q) => $q->where('name', 'In Progress'))
                        ->count(),
                ];
            });

        // Upcoming division activities
        $upcomingActivities = PapActivity::with('pap')
            ->whereHas('pap', function ($query) use ($divisionId) {
                $query->where('division_id', $divisionId);
            })
            ->whereNotNull('start_date')
            ->whereDate('end_date', '>=', today())
            ->orderBy('start_date')
            ->limit(10)
            ->get();

        // Recent division task updates
        $recentTaskUpdates = (clone $divisionTasks)
            ->with(['pap', 'activity', 'activityLogs'])
            ->whereHas('activityLogs')
            ->latest('updated_at')
            ->limit(10)
            ->get();

        return view('dashboards.division-chief', compact(
            'totalPaps',
            'totalActivities',
            'totalTasks',
            'pendingTasks',
            'activeTasks',
            'forReviewTasks',
            'completedTasks',
            'revisionTasks',
            'onHoldTasks',
            'papImplementation',
            'overallImplementation',
            'currentReviews',
            'divisionTaskList',
            'divisionStaff',
            'upcomingActivities',
            'recentTaskUpdates'
        ));
    }



    private function getCurrentTaskStage(Task $task): string
    {
        $stages = collect();

        // Internal reviewers
        foreach ($task->reviews as $review) {

            $stages->push([
                'order' => $review->review_order,
                'status' => $review->status,
                'name' => $review->reviewer?->name ?? 'Unassigned reviewer',
                'type' => 'review',
            ]);
        }

        // External routing destinations
        foreach ($task->routings as $routing) {

            $destination = null;

            if ($routing->destination_type === 'division') {
                $destination = $routing->destinationDivision?->name;
            } elseif ($routing->destination_type === 'office') {
                $destination = $routing->destinationOffice?->name;
            }

            $destination = $destination
                ?? $routing->receiving_office
                ?? 'Unassigned destination';

            $stages->push([
                'order' => $routing->review_order,
                'status' => $routing->status,
                'name' => $destination,
                'type' => 'routing',
            ]);
        }

        if ($stages->isEmpty()) {
            return 'Not assigned';
        }

        // Find the earliest workflow stage that is not yet completed
        $currentOrder = $stages
            ->filter(function ($stage) {
                return !in_array($stage['status'], [
                    'Completed',
                    'Not Applicable',
                    'Cancelled',
                ]);
            })
            ->min('order');

        if ($currentOrder === null) {
            return 'Completed';
        }

        // Include all active reviewers/destinations at the current stage
        $currentNames = $stages
            ->filter(function ($stage) use ($currentOrder) {
                return $stage['order'] == $currentOrder
                    && !in_array($stage['status'], [
                        'Completed',
                        'Not Applicable',
                        'Cancelled',
                    ]);
            })
            ->pluck('name')
            ->unique()
            ->values();

        return $currentNames->isEmpty()
            ? 'Not assigned'
            : $currentNames->implode(', ');
    }

    private function sectorHeadDashboard()
    {
        // ALL PAPs across all divisions
        $paps = Pap::with(['activities', 'division'])
            ->latest()
            ->get();

        $totalPaps = $paps->count();

        $totalActivities = PapActivity::count();

        // ALL tasks across all divisions
        $allTasks = Task::with([
            'pap',
            'activity',
            'status',
            'reviews.reviewer',
            'reviews.workflowStep',
            'routings.destinationDivision',
            'routings.destinationOffice',
            'activityLogs',
        ]);

        $totalTasks = (clone $allTasks)->count();

        $pendingTasks = (clone $allTasks)
            ->whereHas('status', fn($q) => $q->where('name', 'Pending'))
            ->count();

        $activeTasks = (clone $allTasks)
            ->whereHas('status', fn($q) => $q->where('name', 'In Progress'))
            ->count();

        $completedTasks = (clone $allTasks)
            ->whereHas('status', fn($q) => $q->where('name', 'Completed'))
            ->count();

        // PAP IMPLEMENTATION
        $papImplementation = $paps->map(function ($pap) {
            $progress = $pap->activities->isEmpty()
                ? 0
                : round($pap->activities->avg('progress'), 2);

            return [
                'id' => $pap->id,
                'code' => $pap->pap_code,
                'name' => $pap->name,
                'progress' => $progress,
                'activity_count' => $pap->activities->count(),
            ];
        });

        $overallImplementation = $papImplementation->isEmpty()
            ? 0
            : round($papImplementation->avg('progress'), 2);

        // CURRENT REVIEWS
        $reviewTasks = (clone $allTasks)
            ->with([
                'reviews.reviewer',
                'reviews.workflowStep',
                'routings.destinationDivision',
                'routings.destinationOffice',
            ])
            ->where(function ($query) {
                $query->whereHas('reviews')
                    ->orWhereHas('routings');
            })
            ->get();

        
$userId = Auth::id();

// Retrieve review records assigned to the logged-in user
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

// Identify only the user's currently actionable reviews
$currentReviews = $reviewRecords
    ->filter(function ($review) {

        $task = $review->task;

        if (!$task || $task->status?->name === 'For Revision') {
            return false;
        }

        $workflowOrders = $task->reviews
            ->pluck('review_order')
            ->merge($task->routings->pluck('review_order'))
            ->unique()
            ->sort()
            ->values();

        $activeOrder = null;

        foreach ($workflowOrders as $order) {

            $groupReviews = $task->reviews
                ->where('review_order', $order);

            $groupRoutings = $task->routings
                ->where('review_order', $order);

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

        $forwarded = $task->activityLogs->contains(function ($log) use ($activeOrder) {
            return $log->action === 'Workflow Group Forwarded'
                && (int) data_get($log->details, 'review_order')
                === (int) $activeOrder;
        });

        if (!$forwarded) {
            return false;
        }

        return (int) $review->review_order === (int) $activeOrder
            && !in_array($review->status, [
                'Completed',
                'Not Applicable',
                'Returned',
            ]);
    })
    ->unique('task_id')
    ->values();

$pendingReviews = $currentReviews->count();


        // TASK OVERVIEW BY PAP ACTIVITY
        $activities = PapActivity::with([
            'pap',
            'tasks.status',
            'tasks.reviews.reviewer',
            'tasks.reviews.workflowStep',
            'tasks.routings.destinationDivision',
            'tasks.routings.destinationOffice',
        ])->get();

        $taskOverview = $activities->map(function ($activity) {
            $categories = [
                'concept_note' => null,
                'apf' => null,
                'memo' => null,
                'par' => null,
            ];

            foreach ($activity->tasks as $task) {
                $title = strtolower($task->title);

                $category = null;

                if (str_contains($title, 'concept note')) {
                    $category = 'concept_note';
                } elseif (str_contains($title, 'apf')) {
                    $category = 'apf';
                } elseif (str_contains($title, 'memo')) {
                    $category = 'memo';
                } elseif (str_contains($title, 'par')) {
                    $category = 'par';
                }

                if ($category && !$categories[$category]) {
                    $categories[$category] = [
                        'id' => $task->id,
                        'title' => $task->title,
                        'status' => $task->status?->name ?? 'No Status',
                        'current_stage' => $this->getCurrentTaskStage($task),
                    ];
                }
            }

            return [
                'activity_title' => $activity->title,
                'pap_name' => $activity->pap?->name ?? 'No PAP assigned',
                'categories' => $categories,
            ];
        });

        // DIVISION PERFORMANCE
        $officeId = Auth::user()->office_id;

$divisionPerformance = Division::with('users')
    ->where('office_id', $officeId)
    ->get()
    ->map(function ($division) use ($paps) {
        $divisionPaps = $paps->where('division_id', $division->id);

        $divisionTasks = Task::whereHas('pap', function ($query) use ($division) {
            $query->where('division_id', $division->id);
        });

        $papProgress = $divisionPaps->map(function ($pap) {
            return $pap->activities->isEmpty()
                ? 0
                : $pap->activities->avg('progress');
        });

        return [
            'name' => $division->name,
            'total_paps' => $divisionPaps->count(),
            'implementation' => $papProgress->isEmpty()
                ? 0
                : round($papProgress->avg(), 1),
            'total_tasks' => (clone $divisionTasks)->count(),
            'completed_tasks' => (clone $divisionTasks)
                ->whereHas('status', fn($q) => $q->where('name', 'Completed'))
                ->count(),
            'active_tasks' => (clone $divisionTasks)
                ->whereHas('status', fn($q) => $q->where('name', 'In Progress'))
                ->count(),
        ];
    });

        // STAFF WORKLOAD ACROSS ALL DIVISIONS
        $staffWorkload = User::whereHas('roles', function ($query) {
            $query->where('name', 'Staff');
        })
            ->with('division')
            ->get()
            ->map(function ($staff) {
                $staffTasks = Task::where(function ($query) use ($staff) {
                    $query->where('assigned_to', $staff->id)
                        ->orWhereHas('assignments', function ($assignmentQuery) use ($staff) {
                            $assignmentQuery->where('user_id', $staff->id);
                        });
                });

                return [
                    'name' => $staff->name,
                    'division' => $staff->division?->name ?? 'No Division',
                    'total_tasks' => (clone $staffTasks)->count(),
                    'active_tasks' => (clone $staffTasks)
                        ->whereHas('status', fn($q) => $q->where('name', 'In Progress'))
                        ->count(),
                    'completed_tasks' => (clone $staffTasks)
                        ->whereHas('status', fn($q) => $q->where('name', 'Completed'))
                        ->count(),
                ];
            })
            ->sortByDesc('total_tasks')
            ->values();

        // UPCOMING PAP ACTIVITIES ACROSS ALL DIVISIONS
        $upcomingActivities = PapActivity::with('pap')
            ->whereNotNull('start_date')
            ->whereDate('end_date', '>=', today())
            ->orderBy('start_date')
            ->limit(10)
            ->get();

        // UPCOMING TASKS ACROSS ALL DIVISIONS
        $upcomingTasks = Task::with(['pap', 'status'])
            ->where(function ($query) {
                $query->whereDate('due_date', '>=', today())
                    ->orWhere(function ($subQuery) {
                        $subQuery->whereNull('due_date')
                            ->whereDate('start_date', '>=', today());
                    });
            })
            ->orderByRaw('COALESCE(due_date, start_date) ASC')
            ->limit(10)
            ->get();

        return view('dashboards.sector-head', compact(
            'totalPaps',
            'totalActivities',
            'totalTasks',
            'activeTasks',
            'pendingTasks',
            'completedTasks',
            'pendingReviews',
            'papImplementation',
            'overallImplementation',
            'taskOverview',
            'upcomingActivities',
            'upcomingTasks',
            'currentReviews',
            'staffWorkload',
            'divisionPerformance'
        ));
    }
}
