<?php

namespace App\Http\Controllers;

use App\Models\FiscalYear;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class FiscalYearManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = FiscalYear::query()
            ->withCount('paps')
            ->orderByDesc('year');

        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($q) use ($search) {
                $q->where('year', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $fiscalYears = $query
            ->paginate(10)
            ->withQueryString();

        $counts = [
            'total' => FiscalYear::count(),
            'draft' => FiscalYear::where('status', 'Draft')->count(),
            'active' => FiscalYear::where('status', 'Active')->count(),
            'closed' => FiscalYear::where('status', 'Closed')->count(),
        ];

        $activeFiscalYear = FiscalYear::where('status', 'Active')->first();

        return view('fiscal-years.index', compact(
            'fiscalYears',
            'counts',
            'activeFiscalYear'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'year' => [
                'required',
                'integer',
                'min:2000',
                'max:2100',
                'unique:fiscal_years,year',
            ],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
        ]);

        FiscalYear::create([
            ...$validated,
            'status' => 'Draft',
        ]);

        return redirect()
            ->route('fiscal-years.index')
            ->with('success', 'Fiscal year created successfully.');
    }

    public function update(Request $request, FiscalYear $fiscalYear)
    {
        $validated = $request->validate([
            'year' => [
                'required',
                'integer',
                'min:2000',
                'max:2100',
                Rule::unique('fiscal_years', 'year')->ignore($fiscalYear->id),
            ],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after:start_date'],
        ]);

        $fiscalYear->update($validated);

        return redirect()
            ->route('fiscal-years.index')
            ->with('success', 'Fiscal year updated successfully.');
    }

    public function activate(FiscalYear $fiscalYear)
    {
        DB::transaction(function () use ($fiscalYear) {
            $fiscalYear = FiscalYear::whereKey($fiscalYear->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($fiscalYear->status === 'Closed') {
                throw ValidationException::withMessages([
                    'status' => 'A closed fiscal year cannot be activated.',
                ]);
            }

            FiscalYear::where('status', 'Active')
                ->where('id', '!=', $fiscalYear->id)
                ->update(['status' => 'Closed']);

            $fiscalYear->update(['status' => 'Active']);
        });

        return redirect()
            ->route('fiscal-years.index')
            ->with('success', "FY {$fiscalYear->year} is now the active fiscal year.");
    }

    public function close(FiscalYear $fiscalYear)
    {
        if ($fiscalYear->status === 'Closed') {
            return back()->with('info', 'This fiscal year is already closed.');
        }

        $fiscalYear->update(['status' => 'Closed']);

        return redirect()
            ->route('fiscal-years.index')
            ->with('success', "FY {$fiscalYear->year} has been closed.");
    }
}