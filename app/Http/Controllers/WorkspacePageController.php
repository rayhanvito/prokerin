<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Dashboard\DashboardPayloadAction;
use App\Actions\Dashboard\DashboardRoleResolverAction;
use App\Actions\Document\ValidateDocumentUploadAction;
use App\Actions\EventRegistration\GetEventRegistrationManagementPayloadAction;
use App\Actions\Project\GetProjectDetailPayloadAction;
use App\Actions\Workspace\GetAdminPanelPayloadAction;
use App\Actions\Workspace\GetCertificatePayloadAction;
use App\Actions\Workspace\GetDocumentUploadCenterPayloadAction;
use App\Actions\Workspace\GetExportQueuePayloadAction;
use App\Actions\Workspace\GetFinanceApprovalPayloadAction;
use App\Actions\Workspace\GetFinanceRealizationPayloadAction;
use App\Actions\Workspace\GetHandoverPayloadAction;
use App\Actions\Workspace\GetLpjChecklistPayloadAction;
use App\Actions\Workspace\GetMeetingMinutePayloadAction;
use App\Actions\Workspace\GetNotificationRulePayloadAction;
use App\Actions\Workspace\GetProjectTemplatePayloadAction;
use App\Actions\Workspace\GetProposalDraftPayloadAction;
use App\Actions\Workspace\GetQrAttendancePayloadAction;
use App\Actions\Workspace\GetRolePermissionPayloadAction;
use App\Actions\Workspace\GetSponsorVendorDetailPayloadAction;
use App\Actions\Workspace\GetSponsorVendorPayloadAction;
use App\Actions\Workspace\GetTaskCalendarPayloadAction;
use App\Actions\Workspace\GetTaskKanbanPayloadAction;
use App\Domain\Document\DocumentVisibility;
use App\DTOs\Document\DocumentUploadCandidateData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

final class WorkspacePageController extends Controller
{
    public function dashboard(
        Request $request,
        DashboardRoleResolverAction $roleResolver,
        DashboardPayloadAction $dashboardPayload,
    ): Response {
        $user = $request->user();
        $organizationId = (int) DB::table('organization_members')
            ->where('user_id', $user?->id)
            ->orderBy('id')
            ->value('organization_id');

        if ($organizationId === 0) {
            $organizationId = (int) DB::table('project_members')
                ->join('projects', 'projects.id', '=', 'project_members.project_id')
                ->where('project_members.user_id', $user?->id)
                ->orderBy('project_members.id')
                ->value('projects.organization_id');
        }

        abort_if($organizationId === 0, 403);

        $variant = $roleResolver->execute($user, $organizationId);

        return Inertia::render('Dashboard/Index', [
            'dashboardVariant' => $variant->value,
            'payload' => $dashboardPayload->execute($user, $organizationId, $variant),
        ]);
    }

    public function prokerIndex(): Response
    {
        return Inertia::render('Proker/Index');
    }

    public function prokerCreate(): Response
    {
        return Inertia::render('Proker/Create');
    }

    public function prokerShow(Request $request, GetProjectDetailPayloadAction $projectDetail, ?string $project = null): Response
    {
        return Inertia::render('Proker/Show', $projectDetail->execute((int) $request->user()->id, $project));
    }

    public function prokerEdit(Request $request, GetProjectDetailPayloadAction $projectDetail, string $project): Response
    {
        return Inertia::render('Proker/Edit', $projectDetail->execute((int) $request->user()->id, $project));
    }

    public function prokerTemplates(GetProjectTemplatePayloadAction $templatePlan): Response
    {
        return Inertia::render('Proker/Templates', [
            'templates' => $templatePlan->execute(),
        ]);
    }

    public function prokerStatusFlow(): Response
    {
        return Inertia::render('Proker/StatusFlow');
    }

    public function organizationSetup(Request $request): Response
    {
        return Inertia::render('Organization/Setup', [
            'organization' => $this->organizationSetupPayload($request),
        ]);
    }

    public function organizationSwitcher(): Response
    {
        return Inertia::render('Organization/Switcher');
    }

    public function organizationPeriods(): Response
    {
        return Inertia::render('Organization/Periods');
    }

    public function organizationCalendar(): Response
    {
        return Inertia::render('Organization/Calendar');
    }

    public function organizationHandover(Request $request, GetHandoverPayloadAction $handover): Response
    {
        return Inertia::render('Organization/Handover', $handover->execute((int) $request->user()->id));
    }

    public function organizationSponsorsVendors(Request $request, GetSponsorVendorPayloadAction $sponsorsVendors): Response
    {
        return Inertia::render('Organization/SponsorsVendors', $sponsorsVendors->execute(
            actorUserId: (int) $request->user()->id,
            search: $request->string('search')->toString(),
            type: $request->string('type')->toString(),
        ));
    }

    public function organizationSponsorVendorDetail(
        Request $request,
        int $sponsorVendor,
        GetSponsorVendorDetailPayloadAction $sponsorVendorDetail,
    ): Response {
        return Inertia::render('Organization/SponsorVendorDetail', $sponsorVendorDetail->execute(
            actorUserId: (int) $request->user()->id,
            sponsorVendorId: $sponsorVendor,
        ));
    }

    public function taskIndex(): Response
    {
        return Inertia::render('Task/Index');
    }

    public function taskKanban(Request $request, GetTaskKanbanPayloadAction $kanbanPayload): Response
    {
        return Inertia::render('Task/Kanban', $kanbanPayload->execute((int) $request->user()->id));
    }

    public function taskCalendar(Request $request, GetTaskCalendarPayloadAction $calendarPayload): Response
    {
        return Inertia::render('Task/Calendar', $calendarPayload->execute((int) $request->user()->id));
    }

    public function taskAssignments(): Response
    {
        return Inertia::render('Task/Assignments');
    }

    public function financeIndex(): Response
    {
        return Inertia::render('Finance/Index');
    }

    public function financeBudgetDraft(): Response
    {
        return Inertia::render('Finance/BudgetDraft');
    }

    public function financeRealization(Request $request, GetFinanceRealizationPayloadAction $financeRealization): Response
    {
        return Inertia::render('Finance/Realization', $financeRealization->execute((int) $request->user()->id));
    }

    public function financeApproval(Request $request, GetFinanceApprovalPayloadAction $financeApproval): Response
    {
        return Inertia::render('Finance/Approval', $financeApproval->execute((int) $request->user()->id));
    }

    public function reportsIndex(): Response
    {
        return Inertia::render('Reports/Index');
    }

    public function proposalEditor(Request $request, GetProposalDraftPayloadAction $proposalDraft): Response
    {
        return Inertia::render('Reports/ProposalEditor', [
            'proposalDraft' => $proposalDraft->execute((int) $request->user()->id),
        ]);
    }

    public function lpjChecklist(Request $request, GetLpjChecklistPayloadAction $lpjChecklist): Response
    {
        return Inertia::render('Reports/LpjChecklist', $lpjChecklist->execute((int) $request->user()->id));
    }

    public function exportQueue(Request $request, GetExportQueuePayloadAction $exportQueue): Response
    {
        return Inertia::render('Reports/ExportQueue', [
            'exportQueue' => $exportQueue->execute((int) $request->user()->id),
        ]);
    }

    public function documentsIndex(): Response
    {
        return Inertia::render('Documents/Index');
    }

    public function documentFolders(): Response
    {
        return Inertia::render('Documents/Folders');
    }

    public function uploadCenter(
        Request $request,
        ValidateDocumentUploadAction $validateUpload,
        GetDocumentUploadCenterPayloadAction $documents,
    ): Response {
        $validation = $validateUpload->execute(
            new DocumentUploadCandidateData(
                originalName: 'proposal-v2.pdf',
                mimeType: 'application/pdf',
                sizeInKilobytes: 2048,
                visibility: DocumentVisibility::Private,
            ),
        );

        return Inertia::render('Documents/UploadCenter', [
            'documents' => $documents->execute((int) $request->user()->id),
            'uploadValidation' => [
                'isValid' => $validation->isValid,
                'errors' => $validation->errors,
                'requiresSignedUrl' => $validation->requiresSignedUrl,
            ],
        ]);
    }

    public function membersIndex(): Response
    {
        return Inertia::render('Members/Index');
    }

    public function memberInvites(): Response
    {
        return Inertia::render('Members/Invites');
    }

    public function memberRoles(GetRolePermissionPayloadAction $rolePermissionMatrix): Response
    {
        return Inertia::render('Members/Roles', [
            'rolePermissions' => $rolePermissionMatrix->execute(),
        ]);
    }

    public function meetingsIndex(Request $request, GetMeetingMinutePayloadAction $meetings): Response
    {
        return Inertia::render('Meetings/Index', $meetings->execute((int) $request->user()->id));
    }

    public function eventRegistrations(Request $request, GetEventRegistrationManagementPayloadAction $eventRegistrations): Response
    {
        return Inertia::render('Events/Registrations', $eventRegistrations->execute((int) $request->user()->id));
    }

    public function attendanceIndex(Request $request, GetQrAttendancePayloadAction $attendance): Response
    {
        return Inertia::render('Attendance/Index', $attendance->execute((int) $request->user()->id));
    }

    public function certificatesIndex(Request $request, GetCertificatePayloadAction $certificates): Response
    {
        return Inertia::render('Certificates/Index', $certificates->execute((int) $request->user()->id));
    }

    public function certificateTemplates(Request $request, GetCertificatePayloadAction $certificates, ?int $template = null): Response
    {
        return Inertia::render('Certificates/Templates', [
            ...$certificates->execute((int) $request->user()->id),
            'selectedTemplateId' => $template,
        ]);
    }

    public function certificateIssue(Request $request, GetCertificatePayloadAction $certificates): Response
    {
        return Inertia::render('Certificates/Issue', $certificates->execute((int) $request->user()->id));
    }

    public function notificationsIndex(Request $request, GetNotificationRulePayloadAction $notificationRules): Response
    {
        return Inertia::render('Notifications/Index', $notificationRules->execute((int) $request->user()->id));
    }

    public function adminIndex(GetAdminPanelPayloadAction $adminPanel): Response
    {
        return Inertia::render('Admin/Index', $adminPanel->execute());
    }

    /**
     * @return array{id: int, name: string, type: string, periodName: string, periodStart: string, periodEnd: string, hasLogo: bool, memberCount: int}|null
     */
    private function organizationSetupPayload(Request $request): ?array
    {
        $organization = DB::table('organizations')
            ->join('organization_members', 'organization_members.organization_id', '=', 'organizations.id')
            ->leftJoin('organization_periods', function ($join): void {
                $join->on('organization_periods.organization_id', '=', 'organizations.id')
                    ->where('organization_periods.is_active', true);
            })
            ->where('organization_members.user_id', $request->user()?->id)
            ->select([
                'organizations.id',
                'organizations.name',
                'organizations.logo_path',
                'organization_periods.name as period_name',
                'organization_periods.starts_at',
                'organization_periods.ends_at',
            ])
            ->orderBy('organization_members.id')
            ->first();

        if ($organization === null) {
            return null;
        }

        return [
            'id' => (int) $organization->id,
            'name' => (string) $organization->name,
            'type' => 'BEM / Executive Board',
            'periodName' => (string) ($organization->period_name ?? 'Belum ada periode aktif'),
            'periodStart' => (string) ($organization->starts_at ?? '-'),
            'periodEnd' => (string) ($organization->ends_at ?? '-'),
            'hasLogo' => filled($organization->logo_path),
            'memberCount' => DB::table('organization_members')
                ->where('organization_id', $organization->id)
                ->count(),
        ];
    }
}
