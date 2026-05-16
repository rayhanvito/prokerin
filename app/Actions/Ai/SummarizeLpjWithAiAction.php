<?php

declare(strict_types=1);

namespace App\Actions\Ai;

use App\Actions\Report\CalculateLpjReadinessAction;
use App\DTOs\Report\LpjChecklistItemData;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

final readonly class SummarizeLpjWithAiAction
{
    public function __construct(
        private EnsureAiFeatureAccessAction $ensureAiFeatureAccess,
        private CalculateLpjReadinessAction $calculateReadiness,
        private AiPromptAction $aiPrompt,
    ) {}

    /**
     * @return array{summary: string, recommendations: array<int, string>, promptHash: string}
     */
    public function execute(int $actorUserId, int $projectId): array
    {
        $payload = $this->buildPromptPayload($actorUserId, $projectId);
        $organizationId = (int) $payload['organization']['id'];

        $this->ensureAiFeatureAccess->execute($actorUserId, $organizationId);

        $result = $this->aiPrompt->execute(
            organizationId: $organizationId,
            userId: $actorUserId,
            actionType: 'lpj_summary',
            payload: $payload,
        );

        $recommendations = is_array($result->content['recommendations'] ?? null)
            ? $result->content['recommendations']
            : [];

        return [
            'summary' => (string) ($result->content['summary'] ?? ''),
            'recommendations' => array_values(array_map('strval', $recommendations)),
            'promptHash' => $result->promptHash,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function buildPromptPayload(int $actorUserId, int $projectId): array
    {
        $project = DB::table('projects')
            ->join('organizations', 'organizations.id', '=', 'projects.organization_id')
            ->join('organization_members', 'organization_members.organization_id', '=', 'organizations.id')
            ->where('projects.id', $projectId)
            ->where('organization_members.user_id', $actorUserId)
            ->first([
                'projects.id',
                'projects.name',
                'projects.description',
                'projects.status',
                'projects.progress',
                'projects.starts_at',
                'projects.ends_at',
                'organizations.id as organization_id',
                'organizations.name as organization_name',
                'organizations.plan_tier',
            ]);

        if ($project === null) {
            throw (new ModelNotFoundException)->setModel('projects', [$projectId]);
        }

        $items = DB::table('lpj_checklist_items')
            ->where('project_id', $projectId)
            ->orderBy('id')
            ->get()
            ->map(static fn (object $item): LpjChecklistItemData => new LpjChecklistItemData(
                title: (string) $item->title,
                isComplete: (bool) $item->is_complete,
                isRequired: (bool) $item->is_required,
            ))
            ->all();

        $readiness = $this->calculateReadiness->execute($items)->toArray();

        return [
            'organization' => [
                'id' => (int) $project->organization_id,
                'name' => (string) $project->organization_name,
                'plan_tier' => (string) $project->plan_tier,
            ],
            'project' => [
                'id' => (int) $project->id,
                'name' => (string) $project->name,
                'description' => (string) ($project->description ?? ''),
                'status' => (string) $project->status,
                'progress' => (int) $project->progress,
                'starts_at' => (string) ($project->starts_at ?? ''),
                'ends_at' => (string) ($project->ends_at ?? ''),
            ],
            'checklist_items' => array_map(
                static fn (LpjChecklistItemData $item): array => [
                    'title' => $item->title,
                    'is_complete' => $item->isComplete,
                    'is_required' => $item->isRequired,
                ],
                $items,
            ),
            'readiness' => [
                'required_item_count' => $readiness['requiredItemCount'],
                'completed_required_item_count' => $readiness['completedRequiredItemCount'],
                'completion_progress' => $readiness['completionProgress'],
                'is_ready_for_review' => $readiness['isReadyForReview'],
                'missing_required_items' => $readiness['missingRequiredItems'],
            ],
        ];
    }
}
