<?php

declare(strict_types=1);

namespace App\Actions\Report;

use App\Actions\Project\TransitionProjectStatusAction;
use App\Domain\Organization\OrganizationRole;
use App\Domain\Project\ProjectStatus;
use App\DTOs\Report\LpjChecklistItemData;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final readonly class SubmitLpjForReviewAction
{
    public function __construct(
        private CalculateLpjReadinessAction $calculateReadiness,
        private TransitionProjectStatusAction $transitionProjectStatus,
    ) {}

    /**
     * @throws ValidationException
     */
    public function execute(int $actorUserId, int $projectId): void
    {
        DB::transaction(function () use ($actorUserId, $projectId): void {
            $project = DB::table('projects')
                ->join('organization_members', 'organization_members.organization_id', '=', 'projects.organization_id')
                ->where('projects.id', $projectId)
                ->where('organization_members.user_id', $actorUserId)
                ->whereIn('organization_members.role', [
                    OrganizationRole::Owner->value,
                    OrganizationRole::Admin->value,
                    OrganizationRole::Secretary->value,
                ])
                ->select(['projects.id', 'projects.status'])
                ->lockForUpdate()
                ->first();

            if ($project === null) {
                throw new NotFoundHttpException('Project was not found for the active workspace.');
            }

            $readiness = $this->calculateReadiness->execute($this->items((int) $project->id));

            if (! $readiness->isReadyForReview) {
                throw ValidationException::withMessages([
                    'lpj' => 'LPJ checklist must be complete before review submission.',
                ]);
            }

            try {
                $targetStatus = $this->transitionProjectStatus->execute(
                    ProjectStatus::from((string) $project->status),
                    ProjectStatus::LpjReview,
                );
            } catch (DomainException) {
                throw ValidationException::withMessages([
                    'lpj' => 'LPJ can only be submitted from a running proker.',
                ]);
            }

            DB::table('projects')
                ->where('id', (int) $project->id)
                ->update([
                    'status' => $targetStatus->value,
                    'updated_at' => now(),
                ]);
        });
    }

    /**
     * @return array<int, LpjChecklistItemData>
     */
    private function items(int $projectId): array
    {
        return DB::table('lpj_checklist_items')
            ->where('project_id', $projectId)
            ->orderBy('id')
            ->get()
            ->map(static fn (object $item): LpjChecklistItemData => new LpjChecklistItemData(
                title: (string) $item->title,
                isComplete: (bool) $item->is_complete,
                isRequired: (bool) $item->is_required,
            ))
            ->all();
    }
}
