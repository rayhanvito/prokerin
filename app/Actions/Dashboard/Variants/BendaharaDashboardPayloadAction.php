<?php

declare(strict_types=1);

namespace App\Actions\Dashboard\Variants;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

final class BendaharaDashboardPayloadAction
{
    public function execute(int $actorUserId, int $organizationId): array
    {
        return Cache::remember("dashboard:bendahara:{$organizationId}:{$actorUserId}", 300, function () use ($organizationId): array {
            $rabTotal = (int) DB::table('budget_lines')->join('projects', 'projects.id', '=', 'budget_lines.project_id')->where('projects.organization_id', $organizationId)->sum('budget_lines.planned_amount');
            $realizedTotal = (int) DB::table('budget_lines')->join('projects', 'projects.id', '=', 'budget_lines.project_id')->where('projects.organization_id', $organizationId)->sum('budget_lines.realized_amount');

            return [
                'kpiMetrics' => [
                    ['label' => 'Total RAB Periode', 'value' => 'Rp '.number_format($rabTotal, 0, ',', '.')],
                    ['label' => 'Total Realisasi', 'value' => 'Rp '.number_format($realizedTotal, 0, ',', '.')],
                    ['label' => 'Sisa Anggaran', 'value' => 'Rp '.number_format(max(0, $rabTotal - $realizedTotal), 0, ',', '.')],
                    ['label' => 'Transaksi Pending Approval', 'value' => DB::table('budget_lines')->join('projects', 'projects.id', '=', 'budget_lines.project_id')->where('projects.organization_id', $organizationId)->where('budget_lines.status', 'review')->count()],
                ],
                'pendingTransactions' => $this->pendingTransactions($organizationId),
                'rabVsRealisasiChart' => $this->rabVsRealisasi($organizationId),
                'overBudgetProjects' => array_values(array_filter($this->rabVsRealisasi($organizationId), static fn (array $project): bool => (bool) $project['isOverBudget'])),
                'recentTransactions' => $this->recentTransactions($organizationId),
            ];
        });
    }

    private function pendingTransactions(int $organizationId): array
    {
        return DB::table('budget_lines')
            ->join('projects', 'projects.id', '=', 'budget_lines.project_id')
            ->where('projects.organization_id', $organizationId)
            ->where('budget_lines.status', 'review')
            ->orderByDesc('budget_lines.updated_at')
            ->limit(8)
            ->get(['budget_lines.id', 'budget_lines.name', 'budget_lines.planned_amount', 'projects.name as project_name'])
            ->map(static fn (object $line): array => [
                'id' => (int) $line->id,
                'projectName' => (string) $line->project_name,
                'item' => (string) $line->name,
                'amount' => (int) $line->planned_amount,
            ])
            ->all();
    }

    private function rabVsRealisasi(int $organizationId): array
    {
        return DB::table('projects')
            ->leftJoin('budget_lines', 'budget_lines.project_id', '=', 'projects.id')
            ->where('projects.organization_id', $organizationId)
            ->groupBy('projects.id', 'projects.name')
            ->get(['projects.name', DB::raw('coalesce(sum(budget_lines.planned_amount), 0) as rab_total'), DB::raw('coalesce(sum(budget_lines.realized_amount), 0) as realisasi_total')])
            ->map(static function (object $row): array {
                $rab = (int) $row->rab_total;
                $realized = (int) $row->realisasi_total;
                $usage = $rab === 0 ? 0 : (int) round(($realized / $rab) * 100);

                return [
                    'prokerName' => (string) $row->name,
                    'rabTotal' => $rab,
                    'realisasiTotal' => $realized,
                    'usagePercentage' => $usage,
                    'isOverBudget' => $usage >= 90,
                ];
            })
            ->all();
    }

    private function recentTransactions(int $organizationId): array
    {
        return DB::table('budget_transactions')
            ->join('budget_lines', 'budget_lines.id', '=', 'budget_transactions.budget_line_id')
            ->join('projects', 'projects.id', '=', 'budget_lines.project_id')
            ->where('projects.organization_id', $organizationId)
            ->orderByDesc('budget_transactions.created_at')
            ->limit(10)
            ->get(['budget_transactions.id', 'budget_transactions.name', 'budget_transactions.amount', 'budget_transactions.status', 'budget_transactions.created_at', 'projects.name as project_name'])
            ->map(static fn (object $transaction): array => [
                'id' => (int) $transaction->id,
                'name' => (string) $transaction->name,
                'projectName' => (string) $transaction->project_name,
                'amount' => (int) $transaction->amount,
                'status' => (string) $transaction->status,
                'createdAt' => (string) $transaction->created_at,
            ])
            ->all();
    }
}
