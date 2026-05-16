<?php

declare(strict_types=1);

namespace App\Actions\Approval;

use Illuminate\Support\Facades\DB;

final class SyncApprovalWorkflowSubjectAction
{
    public function execute(int $instanceId): void
    {
        $instance = DB::table('approval_instances')
            ->join('approval_workflow_definitions', 'approval_workflow_definitions.id', '=', 'approval_instances.workflow_definition_id')
            ->where('approval_instances.id', $instanceId)
            ->first([
                'approval_instances.subject_type',
                'approval_instances.subject_id',
                'approval_instances.status',
                'approval_workflow_definitions.workflow_type',
            ]);

        if ($instance === null || (string) $instance->status === 'pending') {
            return;
        }

        match ((string) $instance->subject_type) {
            'proposal_draft' => $this->syncProposalDraft((int) $instance->subject_id, (string) $instance->status),
            'budget_line' => $this->syncBudgetLine((int) $instance->subject_id, (string) $instance->status),
            'project' => $this->syncProject((int) $instance->subject_id, (string) $instance->workflow_type, (string) $instance->status),
            default => null,
        };
    }

    private function syncProposalDraft(int $proposalDraftId, string $workflowStatus): void
    {
        $draft = DB::table('proposal_drafts')
            ->join('projects', 'projects.id', '=', 'proposal_drafts.project_id')
            ->where('proposal_drafts.id', $proposalDraftId)
            ->first([
                'proposal_drafts.id',
                'proposal_drafts.project_id',
                'proposal_drafts.status as draft_status',
                'projects.status as project_status',
            ]);

        if ($draft === null || (string) $draft->draft_status !== 'submitted') {
            return;
        }

        $now = now();
        $isApproved = $workflowStatus === 'approved';

        DB::table('proposal_drafts')
            ->where('id', $proposalDraftId)
            ->update([
                'status' => $isApproved ? 'approved' : 'revision_requested',
                'updated_at' => $now,
            ]);

        if ((string) $draft->project_status === 'proposal_review') {
            DB::table('projects')
                ->where('id', (int) $draft->project_id)
                ->update([
                    'status' => $isApproved ? 'rab_approval' : 'draft',
                    'updated_at' => $now,
                ]);
        }
    }

    private function syncBudgetLine(int $budgetLineId, string $workflowStatus): void
    {
        if (! in_array($workflowStatus, ['approved', 'rejected', 'revision_requested'], true)) {
            return;
        }

        DB::table('budget_lines')
            ->where('id', $budgetLineId)
            ->where('status', 'review')
            ->update([
                'status' => $workflowStatus === 'approved' ? 'approved' : 'rejected',
                'updated_at' => now(),
            ]);
    }

    private function syncProject(int $projectId, string $workflowType, string $workflowStatus): void
    {
        if ($workflowType !== 'lpj') {
            return;
        }

        DB::table('projects')
            ->where('id', $projectId)
            ->where('status', 'lpj_review')
            ->update([
                'status' => $workflowStatus === 'approved' ? 'completed' : 'running',
                'updated_at' => now(),
            ]);
    }
}
