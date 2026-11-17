<?php

namespace App\Http\Controllers;

use App\Models\FundSource;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FundSourceManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = FundSource::query()->latest();

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where(
                'is_active',
                $request->status === 'active'
            );
        }

        $fundSources = $query->paginate(10)->withQueryString();

        $counts = [
            'total' => FundSource::count(),
            'active' => FundSource::where('is_active', true)->count(),
            'inactive' => FundSource::where('is_active', false)->count(),
        ];

        return view('fund-sources.index', compact(
            'fundSources',
            'counts'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => [
                'nullable',
                'string',
                'max:60',
                'unique:fund_sources,code',
            ],
            'name' => [
                'required',
                'string',
                'max:180',
            ],
            'description' => [
                'nullable',
                'string',
            ],
        ]);

        FundSource::create([
            'code' => $validated['code'] ?? null,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'is_active' => true,
        ]);

        return redirect()
            ->route('fund-sources.index')
            ->with('success', 'Fund source added successfully.');
    }

    public function update(Request $request, FundSource $fundSource)
    {
        $validated = $request->validate([
            'code' => [
                'nullable',
                'string',
                'max:60',
                Rule::unique('fund_sources', 'code')->ignore($fundSource->id),
            ],
            'name' => [
                'required',
                'string',
                'max:180',
            ],
            'description' => [
                'nullable',
                'string',
            ],
        ]);

        $fundSource->update($validated);

        return redirect()
            ->route('fund-sources.index')
            ->with('success', 'Fund source updated successfully.');
    }

    public function toggleStatus(FundSource $fundSource)
    {
        $fundSource->update([
            'is_active' => !$fundSource->is_active,
        ]);

        $message = $fundSource->is_active
            ? 'Fund source activated successfully.'
            : 'Fund source deactivated successfully.';

        return redirect()
            ->route('fund-sources.index')
            ->with('success', $message);
    }
}