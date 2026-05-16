<?php

use App\Http\Controllers\ApprovalWorkflowDecisionController;
use App\Http\Controllers\ApprovalWorkflowDelegationController;
use App\Http\Controllers\AttendanceQrCheckInController;
use App\Http\Controllers\BudgetApprovalDecisionController;
use App\Http\Controllers\BudgetReceiptRealizationController;
use App\Http\Controllers\CertificateDownloadController;
use App\Http\Controllers\CertificateIssueController;
use App\Http\Controllers\CertificateTemplateController;
use App\Http\Controllers\CertificateVerificationController;
use App\Http\Controllers\DocumentDownloadController;
use App\Http\Controllers\DocumentExportDownloadController;
use App\Http\Controllers\EventRegistrationController;
use App\Http\Controllers\EventRegistrationExportController;
use App\Http\Controllers\EventRegistrationPdfExportController;
use App\Http\Controllers\EventRegistrationSettingsController;
use App\Http\Controllers\HandoverItemStatusController;
use App\Http\Controllers\HandoverPackageController;
use App\Http\Controllers\HandoverPackageExportController;
use App\Http\Controllers\HandoverPackageStatusController;
use App\Http\Controllers\HandoverPackageTransitionController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\LpjApprovalDecisionController;
use App\Http\Controllers\LpjReviewController;
use App\Http\Controllers\ManualAttendanceController;
use App\Http\Controllers\MeetingWhatsAppAlertController;
use App\Http\Controllers\OrganizationLogoController;
use App\Http\Controllers\OrganizationMemberRoleController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectTemplateGenerationController;
use App\Http\Controllers\ProposalApprovalController;
use App\Http\Controllers\ProposalApprovalDecisionController;
use App\Http\Controllers\ProposalDraftController;
use App\Http\Controllers\SponsorVendorController;
use App\Http\Controllers\TaskDeadlineReminderController;
use App\Http\Controllers\TaskStatusController;
use App\Http\Controllers\WorkspacePageController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LandingController::class, 'home'])->name('landing.home');
Route::get('/features', [LandingController::class, 'features'])->name('landing.features');
Route::get('/pricing', [LandingController::class, 'pricing'])->name('landing.pricing');
Route::get('/verify/{token}', [CertificateVerificationController::class, 'show'])->name('certificates.verify');
Route::get('/events/{project}/register', [EventRegistrationController::class, 'show'])->name('events.register.show');
Route::post('/events/{project}/register', [EventRegistrationController::class, 'store'])->name('events.register.store');

Route::get('/dashboard', [WorkspacePageController::class, 'dashboard'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::prefix('proker')->name('proker.')->group(function () {
        Route::get('/', [WorkspacePageController::class, 'prokerIndex'])->name('index');
        Route::post('/', [ProjectController::class, 'store'])->name('store');
        Route::get('/create', [WorkspacePageController::class, 'prokerCreate'])->name('create');
        Route::get('/sample', [WorkspacePageController::class, 'prokerShow'])->name('show');
        Route::get('/templates', [WorkspacePageController::class, 'prokerTemplates'])->name('templates');
        Route::post('/templates/{template}/generate', [ProjectTemplateGenerationController::class, 'store'])->name('templates.generate');
        Route::get('/status-flow', [WorkspacePageController::class, 'prokerStatusFlow'])->name('status-flow');
        Route::get('/{project}/edit', [WorkspacePageController::class, 'prokerEdit'])->name('edit');
        Route::patch('/{project}', [ProjectController::class, 'update'])->name('update');
        Route::delete('/{project}', [ProjectController::class, 'destroy'])->name('destroy');
        Route::get('/{project}', [WorkspacePageController::class, 'prokerShow'])->name('detail');
    });

    Route::prefix('organization')->name('organization.')->group(function () {
        Route::get('/', [WorkspacePageController::class, 'organizationSetup'])->name('setup');
        Route::post('/logo', [OrganizationLogoController::class, 'store'])->name('logo.store');
        Route::get('/switcher', [WorkspacePageController::class, 'organizationSwitcher'])->name('switcher');
        Route::get('/periods', [WorkspacePageController::class, 'organizationPeriods'])->name('periods');
        Route::get('/calendar', [WorkspacePageController::class, 'organizationCalendar'])->name('calendar');
        Route::get('/handover', [WorkspacePageController::class, 'organizationHandover'])->name('handover');
        Route::get('/sponsors-vendors', [WorkspacePageController::class, 'organizationSponsorsVendors'])->name('sponsors-vendors');
        Route::get('/sponsors-vendors/{sponsorVendor}', [WorkspacePageController::class, 'organizationSponsorVendorDetail'])->name('sponsors-vendors.show');
        Route::post('/sponsors-vendors', [SponsorVendorController::class, 'store'])->name('sponsors-vendors.store');
        Route::patch('/sponsors-vendors/{sponsorVendor}', [SponsorVendorController::class, 'update'])->name('sponsors-vendors.update');
        Route::post('/handover', [HandoverPackageController::class, 'store'])->name('handover.store');
        Route::post('/handover/packages/{package}/export', [HandoverPackageExportController::class, 'store'])->name('handover.packages.export');
        Route::patch('/handover/packages/{package}/status', [HandoverPackageStatusController::class, 'update'])->name('handover.packages.status');
        Route::patch('/handover/packages/{package}/transition', [HandoverPackageTransitionController::class, 'update'])->name('handover.packages.transition');
        Route::patch('/handover/items/{item}', [HandoverItemStatusController::class, 'update'])->name('handover.items.update');
    });

    Route::prefix('tasks')->name('tasks.')->group(function () {
        Route::get('/', [WorkspacePageController::class, 'taskIndex'])->name('index');
        Route::get('/kanban', [WorkspacePageController::class, 'taskKanban'])->name('kanban');
        Route::get('/calendar', [WorkspacePageController::class, 'taskCalendar'])->name('calendar');
        Route::get('/assignments', [WorkspacePageController::class, 'taskAssignments'])->name('assignments');
        Route::patch('/{task}/status', [TaskStatusController::class, 'update'])->name('status.update');
    });

    Route::prefix('finance')->name('finance.')->group(function () {
        Route::get('/', [WorkspacePageController::class, 'financeIndex'])->name('index');
        Route::get('/budget-draft', [WorkspacePageController::class, 'financeBudgetDraft'])->name('budget-draft');
        Route::get('/realization', [WorkspacePageController::class, 'financeRealization'])->name('realization');
        Route::post('/budget-lines/{budgetLine}/realizations', [BudgetReceiptRealizationController::class, 'store'])->name('realizations.store');
        Route::patch('/budget-lines/{budgetLine}/approval', [BudgetApprovalDecisionController::class, 'update'])->name('approvals.update');
        Route::get('/approval', [WorkspacePageController::class, 'financeApproval'])->name('approval');
    });

    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [WorkspacePageController::class, 'reportsIndex'])->name('index');
        Route::get('/proposal-editor', [WorkspacePageController::class, 'proposalEditor'])->name('proposal-editor');
        Route::patch('/proposal-drafts/{proposalDraft}', [ProposalDraftController::class, 'update'])->name('proposal-drafts.update');
        Route::post('/proposal-drafts/{proposalDraft}/submit', [ProposalApprovalController::class, 'store'])->name('proposal-drafts.submit');
        Route::patch('/proposal-drafts/{proposalDraft}/decision', [ProposalApprovalDecisionController::class, 'update'])->name('proposal-drafts.decision');
        Route::get('/lpj-checklist', [WorkspacePageController::class, 'lpjChecklist'])->name('lpj-checklist');
        Route::post('/lpj/{project}/review', [LpjReviewController::class, 'store'])->name('lpj.review');
        Route::patch('/lpj/{project}/decision', [LpjApprovalDecisionController::class, 'update'])->name('lpj.decision');
        Route::get('/exports/{documentExport}/download', [DocumentExportDownloadController::class, 'show'])->name('exports.download');
        Route::get('/export-queue', [WorkspacePageController::class, 'exportQueue'])->name('export-queue');
    });

    Route::prefix('documents')->name('documents.')->group(function () {
        Route::get('/', [WorkspacePageController::class, 'documentsIndex'])->name('index');
        Route::get('/folders', [WorkspacePageController::class, 'documentFolders'])->name('folders');
        Route::get('/upload-center', [WorkspacePageController::class, 'uploadCenter'])->name('upload-center');
        Route::get('/{document}/download', [DocumentDownloadController::class, 'show'])->name('download');
    });

    Route::prefix('members')->name('members.')->group(function () {
        Route::get('/', [WorkspacePageController::class, 'membersIndex'])->name('index');
        Route::get('/invites', [WorkspacePageController::class, 'memberInvites'])->name('invites');
        Route::get('/roles', [WorkspacePageController::class, 'memberRoles'])->name('roles');
        Route::patch('/{member}/role', [OrganizationMemberRoleController::class, 'update'])->name('role.update');
    });

    Route::get('/meetings', [WorkspacePageController::class, 'meetingsIndex'])->name('meetings.index');
    Route::prefix('events')->name('events.')->group(function () {
        Route::get('/registrations', [WorkspacePageController::class, 'eventRegistrations'])->name('registrations.index');
        Route::get('/registrations/export', [EventRegistrationExportController::class, 'show'])->name('registrations.export');
        Route::post('/registrations/{project}/export-pdf', [EventRegistrationPdfExportController::class, 'store'])->name('registrations.export-pdf');
        Route::patch('/registrations/{project}/settings', [EventRegistrationSettingsController::class, 'update'])->name('registrations.settings.update');
    });
    Route::prefix('attendance')->name('attendance.')->group(function () {
        Route::get('/', [WorkspacePageController::class, 'attendanceIndex'])->name('index');
        Route::post('/check-in', [AttendanceQrCheckInController::class, 'store'])->name('check-in.store');
        Route::post('/sessions/{session}/manual-check-in', [ManualAttendanceController::class, 'store'])->name('manual.store');
    });
    Route::prefix('certificates')->name('certificates.')->group(function () {
        Route::get('/', [WorkspacePageController::class, 'certificatesIndex'])->name('index');
        Route::get('/templates', [WorkspacePageController::class, 'certificateTemplates'])->name('templates');
        Route::post('/templates', [CertificateTemplateController::class, 'store'])->name('templates.store');
        Route::get('/templates/{template}/edit', [WorkspacePageController::class, 'certificateTemplates'])->name('templates.edit');
        Route::put('/templates/{template}', [CertificateTemplateController::class, 'update'])->name('templates.update');
        Route::get('/issue', [WorkspacePageController::class, 'certificateIssue'])->name('issue');
        Route::post('/issue', [CertificateIssueController::class, 'store'])->name('issue.store');
        Route::get('/{certificateNumber}/download', [CertificateDownloadController::class, 'show'])->name('download');
    });
    Route::get('/notifications', [WorkspacePageController::class, 'notificationsIndex'])->name('notifications.index');
    Route::post('/notifications/task-deadline-reminders', [TaskDeadlineReminderController::class, 'store'])->name('notifications.task-deadline-reminders.store');
    Route::post('/notifications/meeting-alerts', [MeetingWhatsAppAlertController::class, 'store'])->name('notifications.meeting-alerts.store');
    Route::patch('/approval-workflows/{instance}/decision', [ApprovalWorkflowDecisionController::class, 'update'])->name('approval-workflows.decision');
    Route::patch('/approval-workflows/{instance}/delegate', [ApprovalWorkflowDelegationController::class, 'update'])->name('approval-workflows.delegate');
    Route::get('/admin', [WorkspacePageController::class, 'adminIndex'])->name('admin.index');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
