<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Dashboard\GetDashboardOverviewAction;
use App\Actions\Document\ValidateDocumentUploadAction;
use App\Actions\Project\GetProjectDetailPayloadAction;
use App\Actions\Workspace\GetAdminPanelPayloadAction;
use App\Actions\Workspace\GetDocumentUploadCenterPayloadAction;
use App\Actions\Workspace\GetExportQueuePayloadAction;
use App\Actions\Workspace\GetFinanceApprovalPayloadAction;
use App\Actions\Workspace\GetFinanceRealizationPayloadAction;
use App\Actions\Workspace\GetLpjChecklistPayloadAction;
use App\Actions\Workspace\GetNotificationRulePayloadAction;
use App\Actions\Workspace\GetProjectTemplatePayloadAction;
use App\Actions\Workspace\GetProposalDraftPayloadAction;
use App\Actions\Workspace\GetRolePermissionPayloadAction;
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
    public function dashboard(Request $request, GetDashboardOverviewAction $overview): Response
    {
        return Inertia::render('Dashboard', $overview->execute((int) $request->user()->id));
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

    public function organizationHandover(): Response
    {
        return Inertia::render('Organization/Handover');
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

    public function notificationsIndex(GetNotificationRulePayloadAction $notificationRules): Response
    {
        return Inertia::render('Notifications/Index', [
            'notificationRules' => $notificationRules->execute(),
        ]);
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
