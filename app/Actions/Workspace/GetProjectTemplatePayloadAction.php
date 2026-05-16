<?php

declare(strict_types=1);

namespace App\Actions\Workspace;

use Illuminate\Support\Facades\DB;

final class GetProjectTemplatePayloadAction
{
    /**
     * @return array<int, array{type: string, label: string, plan: array{templateType: string, proposalOutline: string, tasks: array<int, mixed>, budgetLines: array<int, mixed>, lpjChecklist: array<int, string>}}>
     */
    public function execute(): array
    {
        return DB::table('project_templates')
            ->orderBy('id')
            ->get()
            ->map(static fn (object $template): array => [
                'type' => (string) $template->type,
                'label' => (string) $template->label,
                'plan' => [
                    'templateType' => (string) $template->type,
                    'proposalOutline' => (string) $template->proposal_outline,
                    'tasks' => json_decode((string) $template->tasks, true) ?: [],
                    'budgetLines' => json_decode((string) $template->budget_lines, true) ?: [],
                    'lpjChecklist' => json_decode((string) $template->lpj_checklist, true) ?: [],
                ],
            ])
            ->all();
    }
}
