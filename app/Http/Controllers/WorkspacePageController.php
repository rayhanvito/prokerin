<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Dashboard\DashboardPayloadAction;
use App\Actions\Dashboard\DashboardRoleResolverAction;
use App\Actions\Dashboard\KepanitiaanDashboardPayloadAction;
use App\Actions\EventRegistration\GetEventRegistrationManagementPayloadAction;
use App\Actions\Project\GetProjectDetailPayloadAction;
use App\Actions\Workspace\GetAdminPanelPayloadAction;
use App\Actions\Workspace\GetBudgetDraftPayloadAction;
use App\Actions\Workspace\GetCertificatePayloadAction;
use App\Actions\Workspace\GetDocumentFolderTreePayloadAction;
use App\Actions\Workspace\GetDocumentUploadCenterPayloadAction;
use App\Actions\Workspace\GetExportQueuePayloadAction;
use App\Actions\Workspace\GetFinanceApprovalPayloadAction;
use App\Actions\Workspace\GetFinanceOverviewPayloadAction;
use App\Actions\Workspace\GetFinanceRealizationPayloadAction;
use App\Actions\Workspace\GetHandoverPayloadAction;
use App\Actions\Workspace\GetLpjChecklistPayloadAction;
use App\Actions\Workspace\GetMeetingMinutePayloadAction;
use App\Actions\Workspace\GetMemberInvitationsPayloadAction;
use App\Actions\Workspace\GetMembersOverviewPayloadAction;
use App\Actions\Workspace\GetNotificationRulePayloadAction;
use App\Actions\Workspace\GetOrganizationCalendarPayloadAction;
use App\Actions\Workspace\GetOrganizationPeriodsPayloadAction;
use App\Actions\Workspace\GetOrganizationSwitcherPayloadAction;
use App\Actions\Workspace\GetProjectTemplatePayloadAction;
use App\Actions\Workspace\GetProkerIndexPayloadAction;
use App\Actions\Workspace\GetProposalDraftPayloadAction;
use App\Actions\Workspace\GetQrAttendancePayloadAction;
use App\Actions\Workspace\GetReportsOverviewPayloadAction;
use App\Actions\Workspace\GetRolePermissionPayloadAction;
use App\Actions\Workspace\GetSponsorVendorDetailPayloadAction;
use App\Actions\Workspace\GetSponsorVendorPayloadAction;
use App\Actions\Workspace\GetTaskAssignmentsPayloadAction;
use App\Actions\Workspace\GetTaskCalendarPayloadAction;
use App\Actions\Workspace\GetTaskKanbanPayloadAction;
use App\Actions\Workspace\GetTaskOverviewPayloadAction;
use App\Support\OrganizationModeGate;
use Illuminate\Http\RedirectResponse;
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
        KepanitiaanDashboardPayloadAction $kepanitiaanDashboardPayload,
    ): Response|RedirectResponse {
        $user = $request->user();

        // Route platform-level admins to their dedicated panels rather than the org dashboard.
        if ($user !== null) {
            if (method_exists($user, 'hasRole') && $user->hasRole('super_admin')) {
                return redirect()->to('/internal-admin');
            }

            if (DB::table('campuses')->where('admin_user_id', $user->id)->exists()) {
                return redirect()->route('campus.dashboard');
            }
        }

        $organizationId = $this->activeOrganizationId($request);

        abort_if($organizationId === 0, 403);

        if (OrganizationModeGate::forOrganization($organizationId)->isKepanitiaan()) {
            return Inertia::render('KepanitiaanDashboard/Index', [
                'payload' => $kepanitiaanDashboardPayload->execute($organizationId),
            ]);
        }

        $variant = $roleResolver->execute($user, $organizationId);

        return Inertia::render('Dashboard/Index', [
            'dashboardVariant' => $variant->value,
            'payload' => $dashboardPayload->execute($user, $organizationId, $variant),
        ]);
    }

    public function prokerIndex(Request $request, GetProkerIndexPayloadAction $prokerIndex): Response
    {
        $activeOrganizationId = $request->session()->get('active_organization_id');

        return Inertia::render('Proker/Index', $prokerIndex->execute(
            actorUserId: (int) $request->user()->id,
            preferredOrganizationId: is_numeric($activeOrganizationId) ? (int) $activeOrganizationId : null,
            search: $request->string('search')->toString(),
            status: $request->string('status')->toString(),
            period: $request->string('period')->toString(),
        ));
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

    public function organizationSwitcher(Request $request, GetOrganizationSwitcherPayloadAction $switcher): Response
    {
        $activeOrganizationId = $request->session()->get('active_organization_id');

        return Inertia::render('Organization/Switcher', $switcher->execute(
            actorUserId: (int) $request->user()->id,
            preferredOrganizationId: is_numeric($activeOrganizationId) ? (int) $activeOrganizationId : null,
        ));
    }

    public function organizationPeriods(Request $request, GetOrganizationPeriodsPayloadAction $periods): Response
    {
        abort_unless(OrganizationModeGate::forRequest($request)->canUsePeriods(), 403);

        $activeOrganizationId = $request->session()->get('active_organization_id');

        return Inertia::render('Organization/Periods', $periods->execute(
            actorUserId: (int) $request->user()->id,
            preferredOrganizationId: is_numeric($activeOrganizationId) ? (int) $activeOrganizationId : null,
        ));
    }

    public function organizationCalendar(Request $request, GetOrganizationCalendarPayloadAction $calendar): Response
    {
        $activeOrganizationId = $request->session()->get('active_organization_id');

        return Inertia::render('Organization/Calendar', $calendar->execute(
            actorUserId: (int) $request->user()->id,
            preferredOrganizationId: is_numeric($activeOrganizationId) ? (int) $activeOrganizationId : null,
            month: $request->string('month')->toString(),
        ));
    }

    public function organizationHandover(Request $request, GetHandoverPayloadAction $handover): Response
    {
        abort_unless(OrganizationModeGate::forRequest($request)->canUseHandover(), 403);

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

    public function taskIndex(Request $request, GetTaskOverviewPayloadAction $taskOverview): Response
    {
        $activeOrganizationId = $request->session()->get('active_organization_id');

        return Inertia::render('Task/Index', $taskOverview->execute(
            actorUserId: (int) $request->user()->id,
            preferredOrganizationId: is_numeric($activeOrganizationId) ? (int) $activeOrganizationId : null,
        ));
    }

    public function taskKanban(Request $request, GetTaskKanbanPayloadAction $kanbanPayload): Response
    {
        $activeOrganizationId = $request->session()->get('active_organization_id');

        return Inertia::render('Task/Kanban', $kanbanPayload->execute(
            userId: (int) $request->user()->id,
            preferredOrganizationId: is_numeric($activeOrganizationId) ? (int) $activeOrganizationId : null,
        ));
    }

    public function taskCalendar(Request $request, GetTaskCalendarPayloadAction $calendarPayload): Response
    {
        $activeOrganizationId = $request->session()->get('active_organization_id');

        return Inertia::render('Task/Calendar', $calendarPayload->execute(
            userId: (int) $request->user()->id,
            preferredOrganizationId: is_numeric($activeOrganizationId) ? (int) $activeOrganizationId : null,
        ));
    }

    public function taskAssignments(Request $request, GetTaskAssignmentsPayloadAction $assignmentsPayload): Response
    {
        $activeOrganizationId = $request->session()->get('active_organization_id');

        return Inertia::render('Task/Assignments', $assignmentsPayload->execute(
            actorUserId: (int) $request->user()->id,
            preferredOrganizationId: is_numeric($activeOrganizationId) ? (int) $activeOrganizationId : null,
        ));
    }

    public function financeIndex(Request $request, GetFinanceOverviewPayloadAction $financeOverview): Response
    {
        $activeOrganizationId = $request->session()->get('active_organization_id');

        return Inertia::render('Finance/Index', $financeOverview->execute(
            actorUserId: (int) $request->user()->id,
            preferredOrganizationId: is_numeric($activeOrganizationId) ? (int) $activeOrganizationId : null,
        ));
    }

    public function financeBudgetDraft(Request $request, GetBudgetDraftPayloadAction $budgetDraft): Response
    {
        $activeOrganizationId = $request->session()->get('active_organization_id');

        return Inertia::render('Finance/BudgetDraft', $budgetDraft->execute(
            actorUserId: (int) $request->user()->id,
            preferredOrganizationId: is_numeric($activeOrganizationId) ? (int) $activeOrganizationId : null,
        ));
    }

    public function financeRealization(Request $request, GetFinanceRealizationPayloadAction $financeRealization): Response
    {
        return Inertia::render('Finance/Realization', $financeRealization->execute((int) $request->user()->id));
    }

    public function financeApproval(Request $request, GetFinanceApprovalPayloadAction $financeApproval): Response
    {
        return Inertia::render('Finance/Approval', $financeApproval->execute((int) $request->user()->id));
    }

    public function reportsIndex(Request $request, GetReportsOverviewPayloadAction $reportsOverview): Response
    {
        return Inertia::render('Reports/Index', $reportsOverview->execute(
            actorUserId: (int) $request->user()->id,
            preferredOrganizationId: $request->session()->get('active_organization_id') === null
                ? null
                : (int) $request->session()->get('active_organization_id'),
        ));
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

    public function documentFolders(Request $request, GetDocumentFolderTreePayloadAction $folders): Response
    {
        return Inertia::render('Documents/Folders', [
            'folders' => $folders->execute(
                actorUserId: (int) $request->user()->id,
                preferredOrganizationId: $request->session()->get('active_organization_id') === null
                    ? null
                    : (int) $request->session()->get('active_organization_id'),
            ),
        ]);
    }

    public function uploadCenter(
        Request $request,
        GetDocumentUploadCenterPayloadAction $uploadCenter,
    ): Response {
        $payload = $uploadCenter->execute(
            actorUserId: (int) $request->user()->id,
            preferredOrganizationId: $request->session()->get('active_organization_id') === null
                ? null
                : (int) $request->session()->get('active_organization_id'),
        );

        return Inertia::render('Documents/UploadCenter', [
            'documents' => $payload['documents'],
            'projects' => $payload['projects'],
        ]);
    }

    public function membersIndex(Request $request, GetMembersOverviewPayloadAction $members): Response
    {
        $activeOrganizationId = $request->session()->get('active_organization_id');

        return Inertia::render('Members/Index', $members->execute(
            actorUserId: (int) $request->user()->id,
            preferredOrganizationId: is_numeric($activeOrganizationId) ? (int) $activeOrganizationId : null,
        ));
    }

    public function memberInvites(Request $request, GetMemberInvitationsPayloadAction $invitations): Response
    {
        $activeOrganizationId = $request->session()->get('active_organization_id');

        return Inertia::render('Members/Invites', $invitations->execute(
            actorUserId: (int) $request->user()->id,
            preferredOrganizationId: is_numeric($activeOrganizationId) ? (int) $activeOrganizationId : null,
        ));
    }

    public function memberRoles(Request $request, GetRolePermissionPayloadAction $rolePermissionMatrix): Response
    {
        abort_unless(OrganizationModeGate::forRequest($request)->canUseRoleMatrix(), 403);

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
     * @return array{id: int, name: string, description: string, type: string, periodName: string, periodStart: string, periodEnd: string, hasLogo: bool, memberCount: int, canManage: bool}|null
     */
    private function organizationSetupPayload(Request $request): ?array
    {
        $activeOrganizationId = $request->session()->get('active_organization_id');
        $organization = DB::table('organizations')
            ->join('organization_members', 'organization_members.organization_id', '=', 'organizations.id')
            ->leftJoin('organization_periods', function ($join): void {
                $join->on('organization_periods.organization_id', '=', 'organizations.id')
                    ->where('organization_periods.is_active', true);
            })
            ->where('organization_members.user_id', $request->user()?->id)
            ->when(is_numeric($activeOrganizationId), static function ($query) use ($activeOrganizationId): void {
                $query->where('organizations.id', (int) $activeOrganizationId);
            })
            ->select([
                'organizations.id',
                'organizations.name',
                'organizations.description',
                'organizations.logo_path',
                'organizations.mode',
                'organizations.event_date',
                'organizations.auto_archive_at',
                'organization_members.role',
                'organization_periods.name as period_name',
                'organization_periods.starts_at',
                'organization_periods.ends_at',
            ])
            ->orderBy('organization_members.id')
            ->first();

        if ($organization === null && is_numeric($activeOrganizationId)) {
            $request->session()->forget('active_organization_id');

            return $this->organizationSetupPayload($request);
        }

        if ($organization === null) {
            return null;
        }

        return [
            'id' => (int) $organization->id,
            'name' => (string) $organization->name,
            'description' => (string) ($organization->description ?? ''),
            'type' => 'BEM / Executive Board',
            'mode' => (string) ($organization->mode ?? 'organization'),
            'eventDate' => $organization->event_date === null ? null : (string) $organization->event_date,
            'autoArchiveAt' => $organization->auto_archive_at === null ? null : (string) $organization->auto_archive_at,
            'periodName' => (string) ($organization->period_name ?? 'Belum ada periode aktif'),
            'periodStart' => (string) ($organization->starts_at ?? '-'),
            'periodEnd' => (string) ($organization->ends_at ?? '-'),
            'hasLogo' => filled($organization->logo_path),
            'memberCount' => DB::table('organization_members')
                ->where('organization_id', $organization->id)
                ->count(),
            'canManage' => in_array((string) $organization->role, ['organization_owner', 'organization_admin'], true),
        ];
    }

    private function activeOrganizationId(Request $request): int
    {
        $user = $request->user();
        $activeOrganizationId = $request->session()->get('active_organization_id');

        $organizationId = DB::table('organization_members')
            ->where('user_id', $user?->id)
            ->when(is_numeric($activeOrganizationId), static function ($query) use ($activeOrganizationId): void {
                $query->where('organization_id', (int) $activeOrganizationId);
            })
            ->orderBy('id')
            ->value('organization_id');

        if ($organizationId !== null) {
            return (int) $organizationId;
        }

        if (is_numeric($activeOrganizationId)) {
            $request->session()->forget('active_organization_id');
        }

        $fallbackId = DB::table('project_members')
            ->join('projects', 'projects.id', '=', 'project_members.project_id')
            ->where('project_members.user_id', $user?->id)
            ->orderBy('project_members.id')
            ->value('projects.organization_id');

        return $fallbackId === null ? 0 : (int) $fallbackId;
    }
}
