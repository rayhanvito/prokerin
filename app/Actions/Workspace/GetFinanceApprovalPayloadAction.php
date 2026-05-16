<?php

declare(strict_types=1);

namespace App\Actions\Workspace;

use Illuminate\Support\Facades\DB;

final class GetFinanceApprovalPayloadAction
{
    /**
     * @return array{approvals: array<int, array{id: int, title: string, projectName: string, category: string, amount: int, requester: string, status: string, canDecide: bool}>, workflowApprovals: array<int, array{id: int, workflowType: string, subject: string, status: string, currentStep: int, submittedBy: string, canDecide: bool}>, delegateOptions: array<int, array{id: int, name: string}>}
     */
    public function execute(int $actorUserId): array
    {
        $rows = DB::table('budget_lines')
            ->join('projects', 'projects.id', '=', 'budget_lines.project_id')
            ->join('organization_members', 'organization_members.organization_id', '=', 'projects.organization_id')
            ->leftJoin('users', 'users.id', '=', 'projects.project_lead_id')
            ->where('organization_members.user_id', $actorUserId)
            ->whereIn('budget_lines.status', ['review', 'approved', 'rejected'])
            ->select([
                'budget_lines.id',
                'budget_lines.name',
                'budget_lines.category',
                'budget_lines.planned_amount',
                'budget_lines.status',
                'projects.name as project_name',
                'organization_members.role',
                'users.name as requester_name',
            ])
            ->orderByRaw("case budget_lines.status when 'review' then 0 when 'rejected' then 1 else 2 end")
            ->orderBy('budget_lines.id')
            ->get();

        return [
            'approvals' => $rows
                ->map(static fn (object $row): array => [
                    'id' => (int) $row->id,
                    'title' => (string) $row->name,
                    'projectName' => (string) $row->project_name,
                    'category' => (string) $row->category,
                    'amount' => (int) $row->planned_amount,
                    'requester' => (string) ($row->requester_name ?? 'Project Lead'),
                    'status' => (string) $row->status,
                    'canDecide' => (string) $row->status === 'review'
                        && in_array((string) $row->role, ['organization_owner', 'organization_admin', 'treasurer'], true),
                ])
                ->all(),
            'workflowApprovals' => $this->workflowApprovals($actorUserId),
            'delegateOptions' => $this->delegateOptions($actorUserId),
        ];
    }

    /**
     * @return array<int, array{id: int, workflowType: string, subject: string, status: string, currentStep: int, submittedBy: string, canDecide: bool}>
     */
    private function workflowApprovals(int $actorUserId): array
    {
        return DB::table('approval_instances')
            ->join('approval_workflow_definitions', 'approval_workflow_definitions.id', '=', 'approval_instances.workflow_definition_id')
            ->join('approval_step_records', function ($join): void {
                $join->on('approval_step_records.instance_id', '=', 'approval_instances.id')
                    ->on('approval_step_records.step_order', '=', 'approval_instances.current_step');
            })
            ->leftJoin('users as submitters', 'submitters.id', '=', 'approval_instances.submitted_by_user_id')
            ->where('approval_instances.status', 'pending')
            ->where('approval_step_records.approver_id', $actorUserId)
            ->orderBy('approval_instances.id')
            ->get([
                'approval_instances.id',
                'approval_instances.subject_type',
                'approval_instances.subject_id',
                'approval_instances.status',
                'approval_instances.current_step',
                'approval_workflow_definitions.workflow_type',
                'submitters.name as submitter_name',
            ])
            ->map(static fn (object $row): array => [
                'id' => (int) $row->id,
                'workflowType' => (string) $row->workflow_type,
                'subject' => sprintf('%s #%d', (string) $row->subject_type, (int) $row->subject_id),
                'status' => (string) $row->status,
                'currentStep' => (int) $row->current_step,
                'submittedBy' => (string) ($row->submitter_name ?? 'System'),
                'canDecide' => true,
            ])
            ->all();
    }

    /**
     * @return array<int, array{id: int, name: string}>
     */
    private function delegateOptions(int $actorUserId): array
    {
        $organizationIds = DB::table('organization_members')
            ->where('user_id', $actorUserId)
            ->pluck('organization_id');

        return DB::table('organization_members')
            ->join('users', 'users.id', '=', 'organization_members.user_id')
            ->whereIn('organization_members.organization_id', $organizationIds)
            ->where('users.id', '!=', $actorUserId)
            ->orderBy('users.name')
            ->get(['users.id', 'users.name'])
            ->unique('id')
            ->map(static fn (object $user): array => [
                'id' => (int) $user->id,
                'name' => (string) $user->name,
            ])
            ->values()
            ->all();
    }
}
