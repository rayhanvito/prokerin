<?php

declare(strict_types=1);

namespace App\Actions\Workspace;

use App\Actions\Approval\GetApprovalWorkflowTimelineAction;
use App\Actions\Report\CalculateLpjReadinessAction;
use App\Domain\Organization\OrganizationRole;
use App\DTOs\Report\LpjChecklistItemData;
use Illuminate\Support\Facades\DB;

final readonly class GetLpjChecklistPayloadAction
{
    public function __construct(
        private CalculateLpjReadinessAction $readiness,
        private GetApprovalWorkflowTimelineAction $workflowTimeline,
    ) {}

    /**
     * @return array{project: array{id: int|null, status: string|null, canSubmit: bool, canApprove: bool, canExport: bool}, checklistItems: array<int, array{id: int, title: string, isComplete: bool, isRequired: bool}>, readiness: array<string, mixed>, executionSummary: array{completedTasks: int, totalTasks: int, realizedBudget: int, attendanceCount: int}, workflowTimeline: array<string, mixed>}
     */
    public function execute(int $actorUserId): array
    {
        $project = DB::table('projects')
            ->join('organization_members', 'organization_members.organization_id', '=', 'projects.organization_id')
            ->where('organization_members.user_id', $actorUserId)
            ->whereExists(function ($query): void {
                $query->selectRaw('1')
                    ->from('lpj_checklist_items')
                    ->whereColumn('lpj_checklist_items.project_id', 'projects.id');
            })
            ->select(['projects.id', 'projects.status', 'organization_members.role'])
            ->orderBy('projects.id')
            ->first();

        if ($project === null) {
            return $this->emptyPayload();
        }

        $checklistRows = DB::table('lpj_checklist_items')
            ->where('project_id', (int) $project->id)
            ->orderBy('id')
            ->get();

        $items = $checklistRows
            ->map(static fn (object $item): LpjChecklistItemData => new LpjChecklistItemData(
                title: (string) $item->title,
                isComplete: (bool) $item->is_complete,
                isRequired: (bool) $item->is_required,
            ))
            ->all();

        $readiness = $this->readiness->execute($items);

        return [
            'project' => $this->projectPayload($project, $readiness->isReadyForReview),
            'checklistItems' => $checklistRows
                ->map(static fn (object $item): array => [
                    'id' => (int) $item->id,
                    'title' => (string) $item->title,
                    'isComplete' => (bool) $item->is_complete,
                    'isRequired' => (bool) $item->is_required,
                ])
                ->all(),
            'readiness' => $readiness->toArray(),
            'executionSummary' => $this->executionSummary((int) $project->id),
            'workflowTimeline' => $this->workflowTimeline->execute($actorUserId, 'project', (int) $project->id),
        ];
    }

    /**
     * @return array{project: array{id: int|null, status: string|null, canSubmit: bool, canApprove: bool, canExport: bool}, checklistItems: array<int, array{id: int, title: string, isComplete: bool, isRequired: bool}>, readiness: array<string, mixed>, executionSummary: array{completedTasks: int, totalTasks: int, realizedBudget: int, attendanceCount: int}, workflowTimeline: array<string, mixed>}
     */
    private function emptyPayload(): array
    {
        return [
            'project' => [
                'id' => null,
                'status' => null,
                'canSubmit' => false,
                'canApprove' => false,
                'canExport' => false,
            ],
            'checklistItems' => [],
            'readiness' => $this->readiness->execute([])->toArray(),
            'executionSummary' => [
                'completedTasks' => 0,
                'totalTasks' => 0,
                'realizedBudget' => 0,
                'attendanceCount' => 0,
            ],
            'workflowTimeline' => $this->emptyWorkflowTimeline(),
        ];
    }

    /**
     * @return array{id: int, status: string, canSubmit: bool, canApprove: bool, canExport: bool}
     */
    private function projectPayload(object $project, bool $isReadyForReview): array
    {
        $role = (string) $project->role;
        $status = (string) $project->status;

        return [
            'id' => (int) $project->id,
            'status' => $status,
            'canSubmit' => $isReadyForReview
                && $status === 'running'
                && $this->canManageLpj($role),
            'canApprove' => $status === 'lpj_review'
                && in_array($role, [OrganizationRole::Owner->value, OrganizationRole::Admin->value], true),
            'canExport' => $status === 'completed'
                && $this->canManageLpj($role),
        ];
    }

    private function canManageLpj(string $role): bool
    {
        return in_array($role, [
            OrganizationRole::Owner->value,
            OrganizationRole::Admin->value,
            OrganizationRole::Secretary->value,
        ], true);
    }

    /**
     * @return array{completedTasks: int, totalTasks: int, realizedBudget: int, attendanceCount: int}
     */
    private function executionSummary(int $projectId): array
    {
        return [
            'completedTasks' => DB::table('project_tasks')
                ->where('project_id', $projectId)
                ->where('status', 'done')
                ->count(),
            'totalTasks' => DB::table('project_tasks')
                ->where('project_id', $projectId)
                ->count(),
            'realizedBudget' => (int) DB::table('budget_lines')
                ->where('project_id', $projectId)
                ->sum('realized_amount'),
            'attendanceCount' => DB::table('attendance_records')
                ->join('attendance_sessions', 'attendance_sessions.id', '=', 'attendance_records.attendance_session_id')
                ->where('attendance_sessions.project_id', $projectId)
                ->where('attendance_records.status', 'present')
                ->count(),
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
