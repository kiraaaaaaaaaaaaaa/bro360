<?php

namespace App\Http\Controllers;


use App\Models\PapActivity;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    /**
     * Display the Calendar page.
     */
    public function index()
    {
        return view('calendar.index');
    }

    /**
     * Retrieve PAP Activities and Tasks for the Calendar.
     */
    public function events(Request $request)
    {
        $request->validate([
            'start' => 'nullable|date',
            'end' => 'nullable|date',
        ]);

        $start = $request->filled('start')
            ? Carbon::parse($request->start)->toDateString()
            : null;

        $end = $request->filled('end')
            ? Carbon::parse($request->end)->toDateString()
            : null;

        $activities = PapActivity::query()
            ->when($start, function ($query) use ($start) {
                $query->where(function ($q) use ($start) {
                    $q->whereNull('end_date')
                        ->orWhereDate('end_date', '>=', $start);
                });
            })
            ->when($end, function ($query) use ($end) {
                $query->where(function ($q) use ($end) {
                    $q->whereNull('start_date')
                        ->orWhereDate('start_date', '<=', $end);
                });
            })
            ->get();

        $tasks = Task::query()
            ->when($start, function ($query) use ($start) {
                $query->where(function ($q) use ($start) {
                    $q->whereNull('due_date')
                        ->orWhereDate('due_date', '>=', $start);
                });
            })
            ->when($end, function ($query) use ($end) {
                $query->where(function ($q) use ($end) {
                    $q->whereNull('start_date')
                        ->orWhereDate('start_date', '<=', $end);
                });
            })
            ->get();

        $activityEvents = $activities
            ->filter(fn ($activity) => $activity->start_date || $activity->end_date)
            ->map(function ($activity) {
                $eventStart = $activity->start_date ?? $activity->end_date;
                $eventEnd = $activity->end_date ?? $eventStart;

                return [
                    'id' => 'activity-' . $activity->id,
                    'title' => $activity->title,
                    'start' => Carbon::parse($eventStart)->toDateString(),
                    'end' => Carbon::parse($eventEnd)->addDay()->toDateString(),
                    'allDay' => true,
                    'url' => route('activities.show', $activity),
                    'backgroundColor' => '#1c5038',
                    'borderColor' => '#1c5038',
                    'extendedProps' => [
                        'type' => 'PAP Activity',
                    ],
                ];
            });

        $taskEvents = $tasks
            ->filter(fn ($task) => $task->start_date || $task->due_date)
            ->map(function ($task) {
                $eventStart = $task->start_date ?? $task->due_date;
                $eventEnd = $task->due_date ?? $eventStart;

                return [
                    'id' => 'task-' . $task->id,
                    'title' => $task->title,
                    'start' => Carbon::parse($eventStart)->toDateString(),
                    'end' => Carbon::parse($eventEnd)->addDay()->toDateString(),
                    'allDay' => true,
                    'url' => route('tasks.show', $task),
                    'backgroundColor' => '#b28a32',
                    'borderColor' => '#b28a32',
                    'extendedProps' => [
                        'type' => 'Task',
                    ],
                ];
            });

        return response()->json(
            $activityEvents->concat($taskEvents)->values()
        );
    }
}
