<?php

namespace App\Http\Controllers;



use App\Models\Pap;
use App\Models\PapActivity;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ActivityController extends Controller
{
    /**
     * Display all PAP activities.
     */
    public function index()
    {
        $activities = PapActivity::with([
            'pap',
            'focalPerson',
            'creator',
            'tasks.reviews',
        ])
            ->withCount('tasks')
            ->latest()
            ->paginate(15);

        return view('activities.index', compact('activities'));
    }

    /**
     * Show the activity creation form.
     */
    public function create()
    {
        $paps = Pap::orderBy('name')->get();

        $users = User::orderBy('name')->get();

        return view('activities.create', compact(
            'paps',
            'users'
        ));
    }

    /**
     * Save a new PAP activity.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'pap_id' => 'required|exists:paps,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'location' => 'nullable|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'quarter' => 'nullable|in:1,2,3,4',
            'status' => 'required|in:Planned,Ongoing,Completed,Cancelled,Deferred',
            'focal_user_id' => 'nullable|exists:users,id',
        ]);

        $validated['created_by'] = Auth::id();
        $validated['updated_by'] = Auth::id();

        PapActivity::create($validated);

        return redirect()
            ->route('activities.index')
            ->with('success', 'Activity successfully created.');
    }

    /**
     * Display a single activity.
     */
    public function show(PapActivity $activity)
    {
        $activity->load([
            'pap',
            'focalPerson',
            'creator',
            'updater',
            'tasks.assignee',
            'tasks.status',
            'tasks.reviews.reviewer',
            'tasks.reviews',
            'tasks.routings',
        ]);

        return view('activities.show', compact('activity'));
    }

    /**
     * Display the activity editing form.
     */
    public function edit(PapActivity $activity)
    {
        $paps = Pap::orderBy('name')->get();
        $users = User::orderBy('name')->get();

        return view('activities.edit', compact(
            'activity',
            'paps',
            'users'
        ));
    }

    /**
     * Update an existing activity.
     */
    public function update(Request $request, PapActivity $activity)
    {
        $validated = $request->validate([
            'pap_id' => 'required|exists:paps,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'location' => 'nullable|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'quarter' => 'nullable|in:1,2,3,4',
            'status' => 'required|in:Planned,Ongoing,Completed,Cancelled,Deferred',
            'focal_user_id' => 'nullable|exists:users,id',
        ]);

        $validated['updated_by'] = Auth::id();

        $activity->update($validated);

        return redirect()
            ->route('activities.show', $activity)
            ->with('success', 'Activity successfully updated.');
    }
}
