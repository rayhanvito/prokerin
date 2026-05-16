<?php

declare(strict_types=1);

namespace App\Actions\Ai;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;

final readonly class DraftProposalWithAiAction
{
    public function __construct(
        private EnsureAiFeatureAccessAction $ensureAiFeatureAccess,
        private AiPromptAction $aiPrompt,
    ) {}

    /**
     * @return array{sections: array<int, array{heading: string, body: string}>, promptHash: string}
     */
    public function execute(int $actorUserId, int $proposalDraftId): array
    {
        $payload = $this->buildPromptPayload($actorUserId, $proposalDraftId);
        $organizationId = (int) $payload['organization']['id'];

        $this->ensureAiFeatureAccess->execute($actorUserId, $organizationId);

        $result = $this->aiPrompt->execute(
            organizationId: $organizationId,
            userId: $actorUserId,
            actionType: 'proposal_draft',
            payload: $payload,
        );

        $sections = is_array($result->content['sections'] ?? null) ? $result->content['sections'] : [];

        return [
            'sections' => array_values(array_map(
                static fn (array $section): array => [
                    'heading' => (string) ($section['heading'] ?? 'Bagian Proposal'),
                    'body' => (string) ($section['body'] ?? ''),
                ],
                $sections,
            )),
            'promptHash' => $result->promptHash,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function buildPromptPayload(int $actorUserId, int $proposalDraftId): array
    {
        $draft = DB::table('proposal_drafts')
            ->join('projects', 'projects.id', '=', 'proposal_drafts.project_id')
            ->join('organizations', 'organizations.id', '=', 'projects.organization_id')
            ->join('organization_members', 'organization_members.organization_id', '=', 'organizations.id')
            ->where('proposal_drafts.id', $proposalDraftId)
            ->where('organization_members.user_id', $actorUserId)
            ->first([
                'proposal_drafts.id',
                'proposal_drafts.title',
                'proposal_drafts.subtitle',
                'proposal_drafts.sections',
                'proposal_drafts.status',
                'projects.id as project_id',
                'projects.name as project_name',
                'projects.description as project_description',
                'projects.status as project_status',
                'projects.progress',
                'projects.starts_at',
                'projects.ends_at',
                'organizations.id as organization_id',
                'organizations.name as organization_name',
                'organizations.plan_tier',
            ]);

        if ($draft === null) {
            throw (new ModelNotFoundException)->setModel('proposal_drafts', [$proposalDraftId]);
        }

        if (! in_array((string) $draft->status, ['draft', 'revision_requested'], true)) {
            throw new AuthorizationException('Saran AI hanya tersedia untuk draft yang masih dapat diedit.');
        }

        $budgetByCategory = DB::table('budget_lines')
            ->where('project_id', (int) $draft->project_id)
            ->selectRaw('category, SUM(planned_amount) as planned_amount')
            ->groupBy('category')
            ->orderBy('category')
            ->get()
            ->map(static fn (object $line): array => [
                'category' => (string) $line->category,
                'planned_amount' => (int) $line->planned_amount,
            ])
            ->all();

        $sections = json_decode((string) $draft->sections, true) ?: [];

        return [
            'organization' => [
                'id' => (int) $draft->organization_id,
                'name' => (string) $draft->organization_name,
                'plan_tier' => (string) $draft->plan_tier,
            ],
            'project' => [
                'id' => (int) $draft->project_id,
                'name' => (string) $draft->project_name,
                'description' => (string) ($draft->project_description ?? ''),
                'status' => (string) $draft->project_status,
                'progress' => (int) $draft->progress,
                'starts_at' => (string) ($draft->starts_at ?? ''),
                'ends_at' => (string) ($draft->ends_at ?? ''),
            ],
            'proposal' => [
                'id' => (int) $draft->id,
                'title' => (string) $draft->title,
                'subtitle' => (string) $draft->subtitle,
            ],
            'sections' => array_values(array_map(
                static fn (array $section): array => [
                    'heading' => (string) ($section['heading'] ?? ''),
                ],
                $sections,
            )),
            'budget_by_category' => $budgetByCategory,
        ];
    }
}
