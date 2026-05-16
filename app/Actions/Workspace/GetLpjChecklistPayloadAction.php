<?php

declare(strict_types=1);

namespace App\Actions\Workspace;

use App\Actions\Report\CalculateLpjReadinessAction;
use App\Domain\Organization\OrganizationRole;
use App\DTOs\Report\LpjChecklistItemData;
use Illuminate\Support\Facades\DB;

final readonly class GetLpjChecklistPayloadAction
{
    public function __construct(private CalculateLpjReadinessAction $readiness) {}

    /**
     * @return array{project: array{id: int|null, status: string|null, canSubmit: bool, canApprove: bool}, checklistItems: array<int, array{title: string, isComplete: bool, isRequired: bool}>, readiness: array<string, mixed>}
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
            return [
                'project' => [
                    'id' => null,
                    'status' => null,
                    'canSubmit' => false,
                    'canApprove' => false,
                ],
                'checklistItems' => [],
                'readiness' => $this->readiness->execute([])->toArray(),
            ];
        }

        $items = DB::table('lpj_checklist_items')
            ->where('project_id', (int) $project->id)
            ->orderBy('id')
            ->get()
            ->map(static fn (object $item): LpjChecklistItemData => new LpjChecklistItemData(
                title: (string) $item->title,
                isComplete: (bool) $item->is_complete,
                isRequired: (bool) $item->is_required,
            ))
            ->all();

        $readiness = $this->readiness->execute($items);
        $role = (string) $project->role;

        return [
            'project' => [
                'id' => (int) $project->id,
                'status' => (string) $project->status,
                'canSubmit' => $readiness->isReadyForReview
                    && (string) $project->status === 'running'
                    && in_array($role, [
                        OrganizationRole::Owner->value,
                        OrganizationRole::Admin->value,
                        OrganizationRole::Secretary->value,
                    ], true),
                'canApprove' => (string) $project->status === 'lpj_review'
                    && in_array($role, [OrganizationRole::Owner->value, OrganizationRole::Admin->value], true),
            ],
            'checklistItems' => array_map(
                static fn (LpjChecklistItemData $item): array => [
                    'title' => $item->title,
                    'isComplete' => $item->isComplete,
                    'isRequired' => $item->isRequired,
                ],
                $items,
            ),
            'readiness' => $readiness->toArray(),
        ];
    }
}
