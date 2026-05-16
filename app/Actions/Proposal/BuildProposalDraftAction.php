<?php

declare(strict_types=1);

namespace App\Actions\Proposal;

use App\DTOs\Finance\BudgetLineData;
use App\DTOs\Project\ProjectTemplatePlanData;
use App\DTOs\Proposal\ProposalDraftData;
use App\DTOs\Proposal\ProposalProjectData;

final class BuildProposalDraftAction
{
    public function execute(ProposalProjectData $project, ProjectTemplatePlanData $templatePlan): ProposalDraftData
    {
        return new ProposalDraftData(
            title: 'Proposal '.$project->name,
            subtitle: $project->organizationName.' · '.$project->startsAt->format('d M Y'),
            sections: [
                [
                    'heading' => 'Latar Belakang',
                    'body' => $project->description,
                ],
                [
                    'heading' => 'Tujuan Kegiatan',
                    'body' => $templatePlan->proposalOutline,
                ],
                [
                    'heading' => 'Sasaran Peserta',
                    'body' => $project->targetAudience,
                ],
                [
                    'heading' => 'Rundown dan Timeline',
                    'body' => $this->buildTimelineBody($templatePlan),
                ],
                [
                    'heading' => 'RAB Ringkas',
                    'body' => $this->buildBudgetBody($templatePlan->budgetLines),
                ],
                [
                    'heading' => 'Penutup',
                    'body' => sprintf(
                        'Demikian proposal kegiatan ini disusun oleh %s selaku penanggung jawab kegiatan untuk menjadi dasar koordinasi dan persetujuan.',
                        $project->projectLeadName,
                    ),
                ],
            ],
        );
    }

    private function buildTimelineBody(ProjectTemplatePlanData $templatePlan): string
    {
        $taskTitles = array_map(
            static fn ($task): string => $task->title,
            $templatePlan->tasks,
        );

        return implode('; ', $taskTitles).'.';
    }

    /**
     * @param  array<int, BudgetLineData>  $budgetLines
     */
    private function buildBudgetBody(array $budgetLines): string
    {
        $total = array_reduce(
            $budgetLines,
            static fn (int $carry, BudgetLineData $line): int => $carry + $line->plannedAmount->amount,
            0,
        );

        return 'Estimasi kebutuhan anggaran awal adalah Rp'.number_format($total, 0, ',', '.').' berdasarkan item RAB template.';
    }
}
