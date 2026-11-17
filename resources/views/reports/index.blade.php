@extends('layouts.app')

@section('title', 'Reports')
@section('page-title', 'Reports')
@section('page-description', 'Generate and review BRO360 reports')

@section('content')

<div class="reports-page">

    <div class="reports-header">
        <div>
            <h2>Reports Management</h2>
            <p>Generate reports for programs, activities, tasks, and budget utilization.</p>
        </div>
    </div>

    <div class="reports-grid">

        <div class="report-card">
            <h3>PAP Accomplishment Report</h3>
            <p>View the implementation status and accomplishments of Programs, Activities, and Projects.</p>
            <button type="button" class="btn-primary">
                Generate Report
            </button>
        </div>

        <div class="report-card">
            <h3>Activity Monitoring Report</h3>
            <p>Review scheduled activities, implementation progress, and activity status.</p>
            <button type="button" class="btn-primary">
                Generate Report
            </button>
        </div>

        <div class="report-card">
            <h3>Task Completion Report</h3>
            <p>Monitor task assignments, reviewer progress, and completion status.</p>
            <button type="button" class="btn-primary">
                Generate Report
            </button>
        </div>

        <div class="report-card">
            <h3>Overdue Tasks Report</h3>
            <p>Identify tasks that have exceeded their assigned deadlines.</p>
            <button type="button" class="btn-primary">
                Generate Report
            </button>
        </div>

        <div class="report-card">
            <h3>Budget Utilization Report</h3>
            <p>Review PAP budget allocations, obligations, expenditures, and balances.</p>
            <button type="button" class="btn-primary">
                Generate Report
            </button>
        </div>

    </div>

</div>

@endsection