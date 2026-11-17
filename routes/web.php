
<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\PapController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\RoleManagementController;
use App\Http\Controllers\OrganizationalManagementController;
use App\Http\Controllers\FiscalYearManagementController;
use App\Http\Controllers\WorkflowTemplateController;
use App\Http\Controllers\FundSourceManagementController;
use App\Http\Controllers\ProgramManagementController;
use App\Http\Controllers\OutputManagementController;
use App\Http\Controllers\GoogleDriveController;
use App\Http\Controllers\GoogleDriveFolderController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TaskTypeController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])
    ->name('auth.google');

Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])
    ->name('auth.google.callback');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('/login', [AuthenticatedSessionController::class, 'store'])
        ->name('login.store');
});

Route::middleware('auth')->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');

    Route::get('/pap-management', [PapController::class, 'index'])
        ->name('paps.index');

    Route::post('/pap-management', [PapController::class, 'store'])
        ->name('paps.store');

    Route::get('/user-management', [UserManagementController::class, 'index'])
        ->name('users.index');

    Route::post('/user-management', [UserManagementController::class, 'store'])
        ->name('users.store');

    Route::put('/user-management/{user}', [UserManagementController::class, 'update'])
        ->name('users.update');

    Route::patch('/user-management/{user}/status', [UserManagementController::class, 'toggleStatus'])
        ->name('users.toggle-status');

    Route::get('/role-management', [RoleManagementController::class, 'index'])
        ->name('roles.index');

    Route::post('/role-management', [RoleManagementController::class, 'store'])
        ->name('roles.store');

    Route::put('/role-management/{role}', [RoleManagementController::class, 'update'])
        ->name('roles.update');

    Route::delete('/role-management/{role}', [RoleManagementController::class, 'destroy'])
        ->name('roles.destroy');

    Route::middleware('can:organization-management.view')->group(function () {
        Route::get('/organizational-management', [
            OrganizationalManagementController::class,
            'index'
        ])->name('organization.index');
    });

    Route::middleware('can:organization-management.create')->group(function () {

        Route::post('/organizational-management/offices', [
            OrganizationalManagementController::class,
            'storeOffice'
        ])->name('organization.offices.store');

        Route::post('/organizational-management/divisions', [
            OrganizationalManagementController::class,
            'storeDivision'
        ])->name('organization.divisions.store');

        Route::post('/organizational-management/units', [
            OrganizationalManagementController::class,
            'storeUnit'
        ])->name('organization.units.store');

        Route::post('/organizational-management/positions', [
            OrganizationalManagementController::class,
            'storePosition'
        ])->name('organization.positions.store');

        Route::resource('task-types', TaskTypeController::class)
            ->except(['create', 'edit']);
    });

    Route::middleware('can:organization-management.update')->group(function () {

        Route::put('/organizational-management/offices/{office}', [
            OrganizationalManagementController::class,
            'updateOffice'
        ])->name('organization.offices.update');

        Route::put('/organizational-management/divisions/{division}', [
            OrganizationalManagementController::class,
            'updateDivision'
        ])->name('organization.divisions.update');

        Route::put('/organizational-management/units/{unit}', [
            OrganizationalManagementController::class,
            'updateUnit'
        ])->name('organization.units.update');

        Route::put('/organizational-management/positions/{position}', [
            OrganizationalManagementController::class,
            'updatePosition'
        ])->name('organization.positions.update');

        Route::patch('/organizational-management/{type}/{id}/status', [
            OrganizationalManagementController::class,
            'toggleStatus'
        ])->name('organization.status');

        Route::get('/fiscal-years', [FiscalYearManagementController::class, 'index'])
            ->name('fiscal-years.index');

        Route::post('/fiscal-years', [FiscalYearManagementController::class, 'store'])
            ->name('fiscal-years.store');

        Route::put('/fiscal-years/{fiscalYear}', [FiscalYearManagementController::class, 'update'])
            ->name('fiscal-years.update');

        Route::patch('/fiscal-years/{fiscalYear}/activate', [FiscalYearManagementController::class, 'activate'])
            ->name('fiscal-years.activate');

        Route::patch('/fiscal-years/{fiscalYear}/close', [FiscalYearManagementController::class, 'close'])
            ->name('fiscal-years.close');

        Route::get('/workflow-templates', [
            WorkflowTemplateController::class,
            'index'
        ])->name('workflow-templates.index');

        Route::post('/workflow-templates', [
            WorkflowTemplateController::class,
            'store'
        ])->name('workflow-templates.store');

        Route::post('/workflow-templates/{workflowTemplate}/steps', [
            WorkflowTemplateController::class,
            'storeStep'
        ])->name('workflow-templates.steps.store');

        Route::put('/workflow-templates/{workflowTemplate}', [
            WorkflowTemplateController::class,
            'update'
        ])->name('workflow-templates.update');

        Route::post('/workflow-templates/{workflowTemplate}/toggle-status', [
            WorkflowTemplateController::class,
            'toggleStatus'
        ])->name('workflow-templates.toggle-status');

        Route::put('/workflow-templates/{workflowTemplate}/steps/{workflowStep}', [
            WorkflowTemplateController::class,
            'updateStep'
        ])->name('workflow-templates.steps.update');

        Route::delete('/workflow-templates/{workflowTemplate}/steps/{workflowStep}', [
            WorkflowTemplateController::class,
            'destroyStep'
        ])->name('workflow-templates.steps.destroy');

        Route::put('/workflow-templates/{workflowTemplate}/reorder-steps', [
            WorkflowTemplateController::class,
            'reorderSteps'
        ])->name('workflow-templates.steps.reorder');

        Route::post('/workflow-templates/{workflowTemplate}/steps/{workflowStep}/assignees', [
            WorkflowTemplateController::class,
            'storeAssignee'
        ])->name('workflow-templates.assignees.store');

        Route::delete('/workflow-templates/{workflowTemplate}/steps/{workflowStep}/assignees/{assignee}', [
            WorkflowTemplateController::class,
            'destroyAssignee'
        ])->name('workflow-templates.assignees.destroy');

        Route::delete('/workflow-templates/{workflowTemplate}', [
            WorkflowTemplateController::class,
            'destroy'
        ])->name('workflow-templates.destroy');

        Route::get('/fund-sources', [FundSourceManagementController::class, 'index'])
            ->name('fund-sources.index');

        Route::post('/fund-sources', [FundSourceManagementController::class, 'store'])
            ->name('fund-sources.store');

        Route::put('/fund-sources/{fundSource}', [FundSourceManagementController::class, 'update'])
            ->name('fund-sources.update');

        Route::post('/fund-sources/{fundSource}/toggle-status', [FundSourceManagementController::class, 'toggleStatus'])
            ->name('fund-sources.toggle-status');

        Route::get('/programs', [ProgramManagementController::class, 'index'])
            ->name('programs.index');

        Route::post('/programs', [ProgramManagementController::class, 'store'])
            ->name('programs.store');

        Route::put('/programs/{program}', [ProgramManagementController::class, 'update'])
            ->name('programs.update');

        Route::post('/programs/{program}/toggle-status', [
            ProgramManagementController::class,
            'toggleStatus'
        ])->name('programs.toggle-status');





        Route::prefix('programs/{program}/outputs')
            ->name('programs.outputs.')
            ->group(function () {

                Route::get('/', [OutputManagementController::class, 'index'])
                    ->middleware('can:outputs.view')
                    ->name('index');

                Route::post('/', [OutputManagementController::class, 'store'])
                    ->middleware('can:outputs.create')
                    ->name('store');

                Route::put('/{output}', [OutputManagementController::class, 'update'])
                    ->middleware('can:outputs.update')
                    ->name('update');

                Route::post('/{output}/toggle-status', [
                    OutputManagementController::class,
                    'toggleStatus'
                ])
                    ->middleware('can:outputs.update')
                    ->name('toggle-status');
            });
    });

    Route::middleware('auth')
        ->prefix('google/drive')
        ->name('google.drive.')
        ->group(function () {

            Route::get('/connect', [GoogleDriveController::class, 'connect'])
                ->name('connect');

            Route::get('/callback', [GoogleDriveController::class, 'callback'])
                ->name('callback');
        });

    Route::middleware('auth')
        ->prefix('google/drive')
        ->name('google.drive.')
        ->group(function () {

            Route::get('/folders', [GoogleDriveFolderController::class, 'index'])
                ->name('folders.index');

            Route::post('/folders/root', [GoogleDriveFolderController::class, 'createRootFolder'])
                ->name('folders.root.create');
        });

    Route::get('/tasks', [TaskController::class, 'index'])
        ->name('tasks.index');

    Route::get('/tasks/create', [TaskController::class, 'create'])
        ->name('tasks.create');

    Route::post('/tasks', [TaskController::class, 'store'])
        ->name('tasks.store');

    Route::get('/tasks/{task}', [TaskController::class, 'show'])
        ->name('tasks.show');

    Route::get('/tasks', [TaskController::class, 'index'])
        ->name('tasks.index');

    Route::get('/tasks/create', [TaskController::class, 'create'])
        ->name('tasks.create');

    Route::post('/tasks', [TaskController::class, 'store'])
        ->name('tasks.store');

    Route::get('/tasks/{task}', [TaskController::class, 'show'])
        ->name('tasks.show');

    // PAP Activity Management
    Route::get('/activities', [ActivityController::class, 'index'])
        ->name('activities.index');

    Route::get('/activities/create', [ActivityController::class, 'create'])
        ->name('activities.create');

    Route::post('/activities', [ActivityController::class, 'store'])
        ->name('activities.store');

    Route::get('/activities/{activity}', [ActivityController::class, 'show'])
        ->name('activities.show');

    Route::get('/activities/{activity}/edit', [ActivityController::class, 'edit'])
        ->name('activities.edit');

    Route::put('/activities/{activity}', [ActivityController::class, 'update'])
        ->name('activities.update');

    Route::get('/paps/{pap}', [PapController::class, 'show'])
        ->name('paps.show');

    Route::post('/tasks/{task}/submit-for-review', [
        TaskController::class,
        'submitForReview'
    ])->name('tasks.submit-for-review');

    Route::get('/my-tasks', [TaskController::class, 'myTasks'])
        ->name('tasks.my');

    Route::post('/tasks/{task}/attachments', [TaskController::class, 'uploadAttachment'])
        ->name('tasks.attachments.upload');

    Route::get('/task-attachments/{attachment}/download', [TaskController::class, 'downloadAttachment'])
        ->name('tasks.attachments.download');

    Route::put('/tasks/{task}/reviews/{review}/status', [TaskController::class, 'updateReviewerStatus'])
        ->name('tasks.reviews.update-status');

    Route::put('/tasks/{task}/routings/{routing}/status', [TaskController::class, 'updateRoutingStatus'])
        ->name('tasks.routings.update-status');

    Route::get('/my-reviews', [TaskController::class, 'myReviews'])
        ->name('tasks.my-reviews');

    Route::put(
        '/tasks/{task}/reviews/{review}/reviewer-action',
        [TaskController::class, 'reviewerAction']
    )->name('tasks.reviews.reviewer-action');

    Route::post(
        '/tasks/{task}/resubmit-for-review',
        [TaskController::class, 'resubmitForReview']
    )->name('tasks.resubmit-for-review');

    Route::get('/liaison/task/{token}', [TaskController::class, 'liaisonView'])
        ->name('liaison.task.show');

    Route::post(
        '/liaison/task/{token}/routing/{routing}/update',
        [TaskController::class, 'liaisonUpdateRouting']
    )->name('liaison.routing.update');

    Route::get(
        '/tasks/{task}/qr-code',
        [TaskController::class, 'qrCode']
    )->name('tasks.qr-code');

    Route::post('/tasks/{task}/start', [TaskController::class, 'startTask'])
        ->name('tasks.start');

    // NOTIFICATION CENTER
    Route::get('/notifications', [NotificationController::class, 'index'])
        ->name('notifications.index');

    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])
        ->name('notifications.read');

    Route::patch('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])
        ->name('notifications.markAllAsRead');

    Route::patch('/tasks/{task}/reassign', [TaskController::class, 'reassign'])
        ->name('tasks.reassign');

    Route::get('/notifications/{notification}/open', [NotificationController::class, 'open'])
        ->name('notifications.open');

    // DOCUMENT MANAGEMENT
    Route::get('/documents', [DocumentController::class, 'index'])
        ->name('documents.index');

    Route::get('/documents/create', [DocumentController::class, 'create'])
        ->name('documents.create');

    Route::post('/documents', [DocumentController::class, 'store'])
        ->name('documents.store');

    Route::get('/documents/{document}/preview', [DocumentController::class, 'preview'])
        ->name('documents.preview');

    Route::get(
        '/tasks/attachments/{attachment}/preview',
        [TaskController::class, 'previewAttachment']
    )->name('tasks.attachments.preview');

    // Calendar
    Route::get('/calendar', [CalendarController::class, 'index'])
        ->name('calendar.index');

    Route::get('/calendar/events', [CalendarController::class, 'events'])
        ->name('calendar.events');

    Route::get('/reports', [ReportController::class, 'index'])
        ->name('reports.index');

    Route::get('/reports/{type}', [ReportController::class, 'generate'])
        ->name('reports.generate');


    Route::get('/tasks/{task}/edit', [TaskController::class, 'edit'])
        ->name('tasks.edit');

    Route::put('/tasks/{task}', [TaskController::class, 'update'])
        ->name('tasks.update');

    Route::delete('/tasks/{task}', [TaskController::class, 'destroy'])
    ->name('tasks.destroy');
});
