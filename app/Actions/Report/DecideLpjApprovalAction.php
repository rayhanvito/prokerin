<?php

declare(strict_types=1);

namespace App\Actions\Report;

use App\Actions\Approval\ProcessApprovalStepAction;
use App\Actions\Project\TransitionProjectStatusAction;
use App\Domain\Organization\OrganizationRole;
use App\Domain\Project\ProjectStatus;
use App\Domain\Report\LpjApprovalDecision;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final readonly class DecideLpjApprovalAction
{
    public function __construct(
        private TransitionProjectStatusAction $transitionProjectStatus,
        private ProcessApprovalStepAction $processApprovalStep,
    ) {}

    /**
     * @throws ValidationException
     */
    public function execute(int $actorUserId, int $projectId, LpjApprovalDecision $decision): void
    {
        DB::transaction(function () use ($actorUserId, $projectId, $decision): void {
            $project = DB::table('projects')
                ->join('organization_members', 'organization_members.organization_id', '=', 'projects.organization_id')
                ->where('projects.id', $projectId)
                ->where('organization_members.user_id', $actorUserId)
                ->whereIn('organization_members.role', [OrganizationRole::Owner->value, OrganizationRole::Admin->value])
                ->select(['projects.id', 'projects.status'])
                ->lockForUpdate()
                ->first();

            if ($project === null) {
                throw new NotFoundHttpException('Project was not found for the active workspace.');
            }

            $targetStatus = $decision === LpjApprovalDecision::Approve
                ? ProjectStatus::Completed
                : ProjectStatus::Running;

            $workflowInstanceId = $this->pendingWorkflowInstanceId('project', $projectId);

            if ($workflowInstanceId !== null) {
                $this->processApprovalStep->execute(
                    actorUserId: $actorUserId,
                    instanceId: $workflowInstanceId,
                    decision: $decision === LpjApprovalDecision::Approve ? 'approved' : 'revision_requested',
                );

                return;
            }

            try {
                $newStatus = $this->transitionProjectStatus->execute(
                    ProjectStatus::from((string) $project->status),
                    $targetStatus,
                );
            } catch (DomainException) {
                throw ValidationException::withMessages([
                    'decision' => 'LPJ decision is not valid for the current proker status.',
                ]);
            }

            DB::table('projects')
                ->where('id', (int) $project->id)
                ->update([
                    'status' => $newStatus->value,
                    'updated_at' => now(),
                ]);
        });
    }

    private function pendingWorkflowInstanceId(string $subjectType, int $subjectId): ?int
    {
        $instanceId = DB::table('approval_instances')
            ->where('subject_type', $subjectType)
            ->where('subject_id', $subjectId)
            ->where('status', 'pending')
            ->orderByDesc('id')
            ->value('id');

        return $instanceId === null ? null : (int) $instanceId;
    }
}
