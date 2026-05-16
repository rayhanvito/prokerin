<?php

declare(strict_types=1);

namespace App\Actions\Workspace;

use App\Actions\Approval\GetApprovalWorkflowTimelineAction;
use Illuminate\Support\Facades\DB;

final readonly class GetProposalDraftPayloadAction
{
    public function __construct(private GetApprovalWorkflowTimelineAction $workflowTimeline) {}

    /**
     * @return array{id: int|null, title: string, subtitle: string, sections: array<int, array{heading: string, body: string}>, status: string, projectSlug: string|null, projectStatus: string|null, canEdit: bool, canSubmit: bool, canDecide: bool, workflowTimeline: array<string, mixed>}
     */
    public function execute(int $actorUserId): array
    {
        $draft = DB::table('proposal_drafts')
            ->join('projects', 'projects.id', '=', 'proposal_drafts.project_id')
            ->join('organization_members', 'organization_members.organization_id', '=', 'projects.organization_id')
            ->where('organization_members.user_id', $actorUserId)
            ->select([
                'proposal_drafts.id',
                'proposal_drafts.title',
                'proposal_drafts.subtitle',
                'proposal_drafts.sections',
                'proposal_drafts.status',
                'projects.slug as project_slug',
                'projects.status as project_status',
            ])
            ->orderBy('proposal_drafts.id')
            ->first();

        if ($draft === null) {
            return [
                'id' => null,
                'title' => 'Proposal',
                'subtitle' => 'Belum ada draft',
                'sections' => [],
                'status' => 'empty',
                'projectSlug' => null,
                'projectStatus' => null,
                'canEdit' => false,
                'canSubmit' => false,
                'canDecide' => false,
                'workflowTimeline' => $this->emptyWorkflowTimeline(),
            ];
        }

        $status = (string) $draft->status;
        $projectStatus = (string) $draft->project_status;
        $role = (string) DB::table('organization_members')
            ->join('projects', 'projects.organization_id', '=', 'organization_members.organization_id')
            ->join('proposal_drafts', 'proposal_drafts.project_id', '=', 'projects.id')
            ->where('proposal_drafts.id', (int) $draft->id)
            ->where('organization_members.user_id', $actorUserId)
            ->value('organization_members.role');

        return [
            'id' => (int) $draft->id,
            'title' => (string) $draft->title,
            'subtitle' => (string) $draft->subtitle,
            'sections' => json_decode((string) $draft->sections, true) ?: [],
            'status' => $status,
            'projectSlug' => (string) $draft->project_slug,
            'projectStatus' => $projectStatus,
            'canEdit' => $status === 'draft' && in_array($projectStatus, ['draft', 'proposal_review'], true)
                || $status === 'revision_requested' && $projectStatus === 'draft',
            'canSubmit' => $status === 'draft' && in_array($projectStatus, ['draft', 'proposal_review'], true),
            'canDecide' => $status === 'submitted' && $projectStatus === 'proposal_review' && in_array($role, ['organization_owner', 'organization_admin'], true),
            'workflowTimeline' => $this->workflowTimeline->execute($actorUserId, 'proposal_draft', (int) $draft->id),
        ];
    }

    /**
     * @return array{id: null, workflowType: null, status: null, currentStep: null, steps: array<int, mixed>}
     */
    private function emptyWorkflowTimeline(): array
    {
        return [
            'id' => null,
            'workflowType' => null,
            'status' => null,
            'currentStep' => null,
            'steps' => [],
        ];
    }
}
