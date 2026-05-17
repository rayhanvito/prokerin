<?php

declare(strict_types=1);

namespace App\Actions\Workspace;

use App\Domain\Finance\BudgetStatus;
use Illuminate\Support\Facades\DB;

final class GetFinanceOverviewPayloadAction
{
    public function __construct(
        private readonly GetActiveOrganizationContextAction $activeOrganizationContext,
    ) {}

    /**
     * @return array{
     *     metrics: array<int, array{label: string, value: int, note: string, tone: string}>,
     *     monthlyRealization: array<int, array{label: string, amount: int}>,
     *     topCategories: array<int, array{category: string, plannedAmount: int, realizedAmount: int}>,
     *     reviewLines: array<int, array{id: int, name: string, projectName: string, category: string, amount: int, status: string}>
     * }
     */
    public function execute(int $actorUserId, ?int $preferredOrganizationId = null): array
    {
        $context = $this->activeOrganizationContext->execute($actorUserId, $preferredOrganizationId);
        $approvedStatuses = [BudgetStatus::Approved->value, BudgetStatus::Realized->value];

        $baseQuery = DB::table('budget_lines')
            ->join('projects', 'projects.id', '=', 'budget_lines.project_id')
            ->where('projects.organization_id', $context->organizationId);

        $approvedPlanned = (clone $baseQuery)
            ->whereIn('budget_lines.status', $approvedStatuses)
            ->sum('budget_lines.planned_amount');
        $approvedRealized = (clone $baseQuery)
            ->whereIn('budget_lines.status', $approvedStatuses)
            ->sum('budget_lines.realized_amount');
        $reviewCount = (clone $baseQuery)
            ->where('budget_lines.status', BudgetStatus::Review->value)
            ->count();

        return [
            'metrics' => [
                ['label' => 'RAB Approved', 'value' => (int) $approvedPlanned, 'note' => 'Total anggaran siap realisasi', 'tone' => 'primary'],
                ['label' => 'Realisasi', 'value' => (int) $approvedRealized, 'note' => 'Transaksi tercatat/verified', 'tone' => 'secondary'],
                ['label' => 'Sisa Anggaran', 'value' => max(0, (int) $approvedPlanned - (int) $approvedRealized), 'note' => 'Approved dikurangi realisasi', 'tone' => 'success'],
                ['label' => 'Review', 'value' => (int) $reviewCount, 'note' => 'Menunggu keputusan finance', 'tone' => $reviewCount > 0 ? 'danger' : 'success'],
            ],
            'monthlyRealization' => $this->monthlyRealization($context->organizationId),
            'topCategories' => $this->topCategories($context->organizationId),
            'reviewLines' => (clone $baseQuery)
                ->where('budget_lines.status', BudgetStatus::Review->value)
                ->orderByDesc('budget_lines.planned_amount')
                ->limit(5)
                ->get([
                    'budget_lines.id',
                    'budget_lines.name',
                    'budget_lines.category',
                    'budget_lines.planned_amount',
                    'budget_lines.status',
                    'projects.name as project_name',
                ])
                ->map(static fn (object $line): array => [
                    'id' => (int) $line->id,
                    'name' => (string) $line->name,
                    'projectName' => (string) $line->project_name,
                    'category' => (string) $line->category,
                    'amount' => (int) $line->planned_amount,
                    'status' => (string) $line->status,
                ])
                ->all(),
        ];
    }

    /**
     * @return array<int, array{label: string, amount: int}>
     */
    private function monthlyRealization(int $organizationId): array
    {
        return DB::table('budget_transactions')
            ->join('budget_lines', 'budget_lines.id', '=', 'budget_transactions.budget_line_id')
            ->join('projects', 'projects.id', '=', 'budget_lines.project_id')
            ->where('projects.organization_id', $organizationId)
            ->where('budget_transactions.status', 'verified')
            ->orderBy('budget_transactions.created_at')
            ->get(['budget_transactions.created_at', 'budget_transactions.amount'])
            ->groupBy(static fn (object $row): string => substr((string) $row->created_at, 0, 7))
            ->map(static fn ($rows, string $month): array => [
                'label' => $month,
                'amount' => (int) $rows->sum('amount'),
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{category: string, plannedAmount: int, realizedAmount: int}>
     */
    private function topCategories(int $organizationId): array
    {
        return DB::table('budget_lines')
            ->join('projects', 'projects.id', '=', 'budget_lines.project_id')
            ->where('projects.organization_id', $organizationId)
            ->select('budget_lines.category')
            ->selectRaw('sum(budget_lines.planned_amount) as planned_amount')
            ->selectRaw('sum(budget_lines.realized_amount) as realized_amount')
            ->groupBy('budget_lines.category')
            ->orderByDesc('planned_amount')
            ->limit(5)
            ->get()
            ->map(static fn (object $row): array => [
                'category' => (string) $row->category,
                'plannedAmount' => (int) $row->planned_amount,
                'realizedAmount' => (int) $row->realized_amount,
            ])
            ->all();
    }
}
