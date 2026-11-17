@extends('layouts.app')

@section('title', $reportTitle)
@section('page-title', $reportTitle)
@section('page-description', 'Configure report parameters')

@section('content')

<div class="reports-page">

    <div class="panel">

        <div class="panel-header">
            <div>
                <h3>Report Parameters</h3>
                <p>Select the coverage of the report you want to generate.</p>
            </div>

            <a href="{{ route('reports.index') }}" class="view-link">
                Back to Reports
            </a>
        </div>

        <form method="GET"
              action="{{ route('reports.generate', $type) }}"
              class="report-filter-form">

            <div class="form-group">
                <label for="fiscal_year_id">Fiscal Year</label>

                <select name="fiscal_year_id" id="fiscal_year_id">
                    <option value="">All Fiscal Years</option>

                    @foreach($fiscalYears as $fiscalYear)
                        <option value="{{ $fiscalYear->id }}"
                            {{ request('fiscal_year_id') == $fiscalYear->id ? 'selected' : '' }}>
                            {{ $fiscalYear->year }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-primary">
                    Generate Report
                </button>
            </div>

        </form>

    </div>

   <div class="panel">

    <div class="panel-header">
        <div>
            <h3>Report Preview</h3>
            <p>{{ $reportTitle }}</p>
        </div>
    </div>

    @if($type === 'pap-accomplishment')

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th> PAP Code </th>
                        <th>PAP Name</th>
                        <th>Number of Activities</th>
                        <th>Average Implementation</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($reportData as $row)
                        <tr>
                            <td>{{ $row['pap_code'] }}</td>
                            <td>{{ $row['pap_name'] }}</td>
                            <td>{{ $row['activity_count'] }}</td>
                            <td>
                                {{ number_format($row['average_progress'], 1) }}%
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center">
                                No PAP records found for the selected fiscal year.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    @endif

</div>

</div>

@endsection