<?php

namespace App\Http\Controllers;

use App\Models\Pap;
use App\Models\PapActivity;
use App\Models\Task;
use App\Models\TaskReview;
use Illuminate\Http\Request;
use App\Models\FiscalYear;

class ReportController extends Controller
{
    public function index()
    {
        return view('reports.index');
    }

    public function generate(Request $request, $type)
{
    $reports = [
        'pap-accomplishment' => 'PAP Accomplishment Report',
        'activity-monitoring' => 'Activity Monitoring Report',
        'task-completion' => 'Task Completion Report',
        'overdue-tasks' => 'Overdue Tasks Report',
        'budget-utilization' => 'Budget Utilization Report',
    ];

    abort_unless(array_key_exists($type, $reports), 404);

    $reportTitle = $reports[$type];

    $fiscalYears = FiscalYear::orderByDesc('year')->get();

    $reportData = collect();

    if ($type === 'pap-accomplishment') {
        $reportData = Pap::with('activities')
            ->when($request->filled('fiscal_year_id'), function ($query) use ($request) {
                $query->where('fiscal_year_id', $request->fiscal_year_id);
            })
            ->orderBy('pap_code')
            ->get()
            ->map(function ($pap) {
                $activities = $pap->activities;

                return [
                    'pap_code' => $pap->pap_code,
                    'pap_name' => $pap->name,
                    'activity_count' => $activities->count(),
                    'average_progress' => $activities->isEmpty()
                        ? 0
                        : round($activities->avg('progress'), 2),
                ];
            });
    }

    return view('reports.generate', compact(
        'type',
        'reportTitle',
        'fiscalYears',
        'reportData'
    ));
}
}