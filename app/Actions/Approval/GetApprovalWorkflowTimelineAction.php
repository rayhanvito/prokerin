<?php

declare(strict_types=1);

namespace App\Actions\Approval;

use Illuminate\Support\Facades\DB;

final class GetApprovalWorkflowTimelineAction
{
    /**
     * @return array{id: int|null, workflowType: string|null, status: string|null, currentStep: int|null, steps: array<int, array{stepOrder: int, approverName: string, decision: string, note: string|null, decidedAt: string|null}>}
     */
    public function execute(int $actorUserId, string $subjectType, int $subjectId): array
    {
        $instance = DB::table('approval_instances')
            ->join('approval_workflow_definitions', 'approval_workflow_definitions.id', '=', 'approval_instances.workflow_definition_id')
            ->join('organization_members', 'organization_members.organization_id', '=', 'approval_workflow_definitions.organization_id')
            ->where('organization_members.user_id', $actorUserId)
            ->where('approval_instances.subject_type', $subjectType)
            ->where('approval_instances.subject_id', $subjectId)
            ->orderByDesc('approval_instances.id')
            ->first([
                'approval_instances.id',
                'approval_instances.status',
                'approval_instances.current_step',
                'approval_workflow_definitions.workflow_type',
            ]);

        if ($instance === null) {
            return [
                'id' => null,
                'workflowType' => null,
                'status' => null,
                'currentStep' => null,
                'steps' => [],
            ];
        }

        $steps = DB::table('approval_step_records')
            ->leftJoin('users', 'users.id', '=', 'approval_step_records.approver_id')
            ->where('approval_step_records.instance_id', (int) $instance->id)
            ->orderBy('approval_step_records.step_order')
            ->get([
                'approval_step_records.step_order',
                'approval_step_records.decision',
                'approval_step_records.note',
                'approval_step_records.decided_at',
                'users.name as approver_name',
            ])
            ->map(static fn (object $step): array => [
                'stepOrder' => (int) $step->step_order,
                'approverName' => (string) ($step->approver_name ?? 'Approver tidak aktif'),
                'decision' => (string) $step->decision,
                'note' => $step->note === null ? null : (string) $step->note,
                'decidedAt' => $step->decided_at === null ? null : (string) $step->decided_at,
            ])
            ->all();

        return [
            'id' => (int) $instance->id,
            'workflowType' => (string) $instance->workflow_type,
            'status' => (string) $instance->status,
            'currentStep' => (int) $instance->current_step,
            'steps' => $steps,
        ];
    }
}
