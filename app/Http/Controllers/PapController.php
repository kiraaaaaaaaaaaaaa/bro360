<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Pap;
use App\Models\FiscalYear;
use App\Models\Division;
use App\Models\PapStatus;
use App\Models\FundSource;
use App\Models\Program;
use App\Models\OutputCategory;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Services\GoogleDriveService;
use Throwable;

class PapController extends Controller
{
    public function index(Request $request)
    {

        $user = Auth::user();
        $query = Pap::with([
            'fiscalYear',
            'division',
            'fundSource',
            'program',
            'focalUser',
            'status',
        ])->withCount([
            'activities',
            'tasks',
        ]);

        // Search by PAP name or code
        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('pap_code', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by fiscal year
        if ($request->filled('fiscal_year_id')) {
            $query->where('fiscal_year_id', $request->fiscal_year_id);
        }

        // Filter by division
        if ($request->filled('division_id')) {
            $query->where('division_id', $request->division_id);
        }

        // Filter by status
        if ($request->filled('status_id')) {
            $query->where('status_id', $request->status_id);
        }

        $paps = $query
            ->orderByDesc('created_at')
            ->paginate(10)
            ->withQueryString();

        // Filter dropdown data
        $fiscalYears = FiscalYear::orderByDesc('year')->get();


        $statuses = PapStatus::where('is_active', true)
            ->orderBy('name')
            ->get();



        $fundSources = FundSource::where('is_active', true)
            ->orderBy('name')
            ->get();

        $programs = Program::where('is_active', true)
            ->orderBy('name')
            ->get();

        $outputCategories = OutputCategory::where('is_active', true)
            ->orderBy('name')
            ->get();

        $users = User::orderBy('name')->get();

        $divisions = Division::where('office_id', $user->office_id)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('paps.index', compact(
            'paps',
            'fiscalYears',
            'divisions',
            'statuses',
            'fundSources',
            'programs',
            'outputCategories',
            'users',

        ));
    }

    public function store(Request $request, GoogleDriveService $googleDrive)
    {
        $validated = $request->validate([
            'fiscal_year_id'     => 'required|exists:fiscal_years,id',
            'division_id'        => 'required|exists:divisions,id',
            'fund_source_id'     => 'required|exists:fund_sources,id',
            'program_id'         => 'required|exists:programs,id',
            'output_category_id' => 'required|exists:output_categories,id',
            'focal_user_id'      => 'nullable|exists:users,id',
            'pap_code'           => 'required|string|max:100|unique:paps,pap_code',
            'name'               => 'required|string|max:255',
            'description'        => 'nullable|string',
            'status_id'          => 'required|exists:pap_statuses,id',
            'start_date'         => 'nullable|date',
            'end_date'           => 'nullable|date|after_or_equal:start_date',
        ]);

        $validated['created_by'] = Auth::id();
        $validated['updated_by'] = Auth::id();

        // Save the PAP first.
        $pap = Pap::create($validated);

        // Automatically create its Google Drive folders.
        try {
            $folders = $googleDrive->ensurePapSubfolders($pap);

            return redirect()
                ->route('paps.index')
                ->with('success', 'PAP successfully added. Google Drive folders created.');
        } catch (Throwable $e) {
            report($e);

            return redirect()
                ->route('paps.index')
                ->with('warning', 'PAP successfully added, but Google Drive folder creation failed. Please check the central Google Drive connection.');
        }
    }
    
    public function show(Pap $pap)
    {
        $pap->load([
            'fiscalYear',
            'division',
            'fundSource',
            'program',
            'outputCategory',
            'focalUser',

            'activities.focalPerson',
            'activities.tasks',

            'tasks.activity',
            'tasks.assignee',
            'tasks.status',
            'tasks.reviews',
            'tasks.routings',
        ]);

        return view('paps.show', compact('pap'));
    }
}
