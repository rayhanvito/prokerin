<?php

declare(strict_types=1);

namespace App\DTOs\Project;

use App\Domain\Project\ProjectTemplateType;
use App\DTOs\Finance\BudgetLineData;

final readonly class ProjectTemplatePlanData
{
    /**
     * @param  array<int, TemplateTaskData>  $tasks
     * @param  array<int, BudgetLineData>  $budgetLines
     */
    public function __construct(
        public ProjectTemplateType $templateType,
        public string $proposalOutline,
        public array $tasks,
        public array $budgetLines,
        public array $lpjChecklist,
    ) {}

    /**
     * @return array{
     *     templateType: string,
     *     proposalOutline: string,
     *     tasks: array<int, array{title: string, division: string, dueOffsetDays: int, status: string}>,
     *     budgetLines: array<int, array{name: string, category: string, plannedAmount: int}>,
     *     lpjChecklist: array<int, string>
     * }
     */
    public function toArray(): array
    {
        return [
            'templateType' => $this->templateType->value,
            'proposalOutline' => $this->proposalOutline,
            'tasks' => array_map(
                static fn (TemplateTaskData $task): array => $task->toArray(),
                $this->tasks,
            ),
            'budgetLines' => array_map(
                static fn (BudgetLineData $line): array => [
                    'name' => $line->name,
                    'category' => $line->category,
                    'plannedAmount' => $line->plannedAmount->amount,
                ],
                $this->budgetLines,
            ),
            'lpjChecklist' => $this->lpjChecklist,
        ];
    }
}
