<?php

declare(strict_types=1);

namespace App\Actions\Workspace;

use App\Domain\Finance\BudgetStatus;
use App\Support\Roles;
use Illuminate\Support\Facades\DB;

final class GetBudgetDraftPayloadAction
{
    public function __construct(
        private readonly GetActiveOrganizationContextAction $activeOrganizationContext,
    ) {}

    /**
     * @return array{
     *     activeOrganizationId: int,
     *     canManage: bool,
     *     summary: array{
     *         plannedTotal: int,
     *         realizedTotal: int,
     *         remainingBudget: int,
     *         realizationProgress: int,
     *         hasOverspend: bool,
     *         lineCount: int,
     *         approvedLineCount: int,
     *     },
     *     statusOptions: array<int, array{value: string, label: string}>,
     *     projects: array<int, array{id: int, name: string}>,
     *     lines: array<int, array{
     *         id: int,
     *         projectId: int,
     *         projectName: string,
     *         name: string,
     *         category: string,
     *         plannedAmount: int,
     *         realizedAmount: int,
     *         status: string,
     *         isEditable: bool,
     *         isDeletable: bool,
     *     }>
     * }
     */
    public function execute(int $actorUserId, ?int $preferredOrganizationId = null): array
    {
        $context = $this->activeOrganizationContext->execute($actorUserId, $preferredOrganizationId);
        $canManage = in_array($context->role, Roles::FINANCE_MANAGERS, true);

        $approvedStatuses = [BudgetStatus::Approved->value, BudgetStatus::Realized->value];

        $rawLines = DB::table('budget_lines')
            ->join('projects', 'projects.id', '=', 'budget_lines.project_id')
            ->where('projects.organization_id', $context->organizationId)
            ->orderBy('projects.name')
            ->orderBy('budget_lines.category')
            ->orderBy('budget_lines.name')
            ->get([
                'budget_lines.id',
                'budget_lines.project_id',
                'budget_lines.name',
                'budget_lines.category',
                'budget_lines.planned_amount',
                'budget_lines.realized_amount',
                'budget_lines.status',
                'projects.name as project_name',
            ]);

        $editableStatuses = [
            BudgetStatus::Draft->value,
            BudgetStatus::Review->value,
            BudgetStatus::Rejected->value,
        ];
        $deletableStatuses = [BudgetStatus::Draft->value, BudgetStatus::Rejected->value];

        $linesWithTransactions = DB::table('budget_transactions')
            ->whereIn('budget_line_id', $rawLines->pluck('id')->all())
            ->select('budget_line_id')
            ->distinct()
            ->pluck('budget_line_id')
            ->all();

        $plannedTotalApproved = 0;
        $realizedTotalApproved = 0;
        $approvedLineCount = 0;

        $lines = $rawLines->map(static function (object $line) use (
            $editableStatuses,
            $deletableStatuses,
            $linesWithTransactions,
            $approvedStatuses,
            &$plannedTotalApproved,
            &$realizedTotalApproved,
            &$approvedLineCount,
        ): array {
            $status = (string) $line->status;
            $planned = (int) $line->planned_amount;
            $realized = (int) $line->realized_amount;
            $isApproved = in_array($status, $approvedStatuses, true);

            if ($isApproved) {
                $plannedTotalApproved += $planned;
                $realizedTotalApproved += $realized;
                $approvedLineCount++;
            }

            return [
                'id' => (int) $line->id,
                'projectId' => (int) $line->project_id,
                'projectName' => (string) $line->project_name,
                'name' => (string) $line->name,
                'category' => (string) $line->category,
                'plannedAmount' => $planned,
                'realizedAmount' => $realized,
                'status' => $status,
                'isEditable' => in_array($status, $editableStatuses, true),
                'isDeletable' => in_array($status, $deletableStatuses, true)
                    && ! in_array((int) $line->id, $linesWithTransactions, true),
            ];
        })->all();

        $remaining = max(0, $plannedTotalApproved - $realizedTotalApproved);
        $progress = $plannedTotalApproved === 0
            ? 0
            : (int) min(100, round(($realizedTotalApproved / $plannedTotalApproved) * 100));

        return [
            'activeOrganizationId' => $context->organizationId,
            'canManage' => $canManage,
            'summary' => [
                'plannedTotal' => $plannedTotalApproved,
                'realizedTotal' => $realizedTotalApproved,
                'remainingBudget' => $remaining,
                'realizationProgress' => $progress,
                'hasOverspend' => $realizedTotalApproved > $plannedTotalApproved,
                'lineCount' => count($lines),
                'approvedLineCount' => $approvedLineCount,
            ],
            'statusOptions' => collect(BudgetStatus::cases())
                ->map(static fn (BudgetStatus $status): array => [
                    'value' => $status->value,
                    'label' => $status->label(),
                ])
                ->all(),
            'projects' => DB::table('projects')
                ->where('organization_id', $context->organizationId)
                ->orderBy('name')
                ->get(['id', 'name'])
                ->map(static fn (object $project): array => [
                    'id' => (int) $project->id,
                    'name' => (string) $project->name,
                ])
                ->all(),
            'lines' => $lines,
        ];
    }
}
