<?php

declare(strict_types=1);

namespace App\Actions\Project;

use App\Actions\Proposal\BuildProposalDraftAction;
use App\Domain\Project\ProjectTemplateType;
use App\DTOs\Proposal\ProposalProjectData;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;

final readonly class GenerateProjectFromTemplateAction
{
    public function __construct(
        private CreateProjectAction $createProject,
        private BuildProjectTemplatePlanAction $buildTemplatePlan,
        private BuildProposalDraftAction $buildProposalDraft,
    ) {}

    /**
     * @return array{id: int, slug: string}
     */
    public function execute(
        int $actorUserId,
        ProjectTemplateType $templateType,
        string $name,
        string $description,
        string $startsAt,
        string $endsAt,
        string $targetAudience = 'Mahasiswa aktif dan pengurus organisasi kampus.',
        ?int $projectLeadId = null,
    ): array {
        return DB::transaction(function () use (
            $actorUserId,
            $templateType,
            $name,
            $description,
            $startsAt,
            $endsAt,
            $targetAudience,
            $projectLeadId,
        ): array {
            $project = $this->createProject->execute(
                actorUserId: $actorUserId,
                name: $name,
                description: $description,
                templateType: $templateType,
                startsAt: $startsAt,
                endsAt: $endsAt,
                projectLeadId: $projectLeadId,
            );

            $projectRow = DB::table('projects')
                ->join('organizations', 'organizations.id', '=', 'projects.organization_id')
                ->leftJoin('users as leads', 'leads.id', '=', 'projects.project_lead_id')
                ->where('projects.id', $project['id'])
                ->select([
                    'projects.id',
                    'projects.starts_at',
                    'projects.ends_at',
                    'organizations.name as organization_name',
                    'leads.name as lead_name',
                ])
                ->first();

            $templatePlan = $this->buildTemplatePlan->execute($templateType);
            $now = now();

            foreach ($templatePlan->tasks as $task) {
                DB::table('project_tasks')->insert([
                    'project_id' => $project['id'],
                    'pic_user_id' => null,
                    'title' => $task->title,
                    'division' => $task->division,
                    'status' => $task->status->value,
                    'due_at' => $this->taskDueDate((string) $projectRow->starts_at, $task->dueOffsetDays),
                    'completed_at' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            foreach ($templatePlan->budgetLines as $budgetLine) {
                DB::table('budget_lines')->insert([
                    'project_id' => $project['id'],
                    'name' => $budgetLine->name,
                    'category' => $budgetLine->category,
                    'planned_amount' => $budgetLine->plannedAmount->amount,
                    'realized_amount' => 0,
                    'status' => $budgetLine->status->value,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            foreach ($templatePlan->lpjChecklist as $checklistItem) {
                DB::table('lpj_checklist_items')->insert([
                    'project_id' => $project['id'],
                    'title' => $checklistItem,
                    'is_required' => true,
                    'is_complete' => false,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }

            $proposalDraft = $this->buildProposalDraft->execute(
                new ProposalProjectData(
                    name: $name,
                    organizationName: (string) $projectRow->organization_name,
                    description: $description,
                    targetAudience: $targetAudience,
                    startsAt: new DateTimeImmutable((string) $projectRow->starts_at),
                    endsAt: new DateTimeImmutable((string) $projectRow->ends_at),
                    projectLeadName: is_string($projectRow->lead_name) ? $projectRow->lead_name : 'Project Lead',
                ),
                $templatePlan,
            )->toArray();

            DB::table('proposal_drafts')->insert([
                'project_id' => $project['id'],
                'title' => $proposalDraft['title'],
                'subtitle' => $proposalDraft['subtitle'],
                'sections' => json_encode($proposalDraft['sections']),
                'status' => 'draft',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            return $project;
        });
    }

    private function taskDueDate(string $startsAt, int $offsetDays): string
    {
        return (new DateTimeImmutable($startsAt))
            ->modify(sprintf('%+d days', $offsetDays))
            ->format('Y-m-d');
    }
}
