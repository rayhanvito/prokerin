<?php

declare(strict_types=1);

namespace App\Actions\Workspace;

use Illuminate\Support\Facades\DB;

final class GetFinanceRealizationPayloadAction
{
    /**
     * @return array{budgetLines: array<int, array{id: int, name: string, projectName: string, plannedAmount: int, realizedAmount: int, status: string}>, transactions: array<int, array{name: string, budget: string, spent: int, receipt: string, status: string}>}
     */
    public function execute(int $actorUserId): array
    {
        $budgetLines = DB::table('budget_lines')
            ->join('projects', 'projects.id', '=', 'budget_lines.project_id')
            ->join('organization_members', 'organization_members.organization_id', '=', 'projects.organization_id')
            ->where('organization_members.user_id', $actorUserId)
            ->select([
                'budget_lines.id',
                'budget_lines.name',
                'budget_lines.planned_amount',
                'budget_lines.realized_amount',
                'budget_lines.status',
                'projects.name as project_name',
            ])
            ->orderBy('projects.starts_at')
            ->orderBy('budget_lines.name')
            ->get();

        $transactions = DB::table('budget_transactions')
            ->join('budget_lines', 'budget_lines.id', '=', 'budget_transactions.budget_line_id')
            ->join('projects', 'projects.id', '=', 'budget_lines.project_id')
            ->join('organization_members', 'organization_members.organization_id', '=', 'projects.organization_id')
            ->leftJoin('documents', 'documents.id', '=', 'budget_transactions.receipt_document_id')
            ->where('organization_members.user_id', $actorUserId)
            ->select([
                'budget_transactions.name',
                'budget_transactions.amount',
                'budget_transactions.status',
                'budget_lines.name as budget_name',
                'documents.id as receipt_document_id',
            ])
            ->orderByDesc('budget_transactions.created_at')
            ->get();

        return [
            'budgetLines' => $budgetLines
                ->map(static fn (object $line): array => [
                    'id' => (int) $line->id,
                    'name' => (string) $line->name,
                    'projectName' => (string) $line->project_name,
                    'plannedAmount' => (int) $line->planned_amount,
                    'realizedAmount' => (int) $line->realized_amount,
                    'status' => (string) $line->status,
                ])
                ->all(),
            'transactions' => $transactions
                ->map(static fn (object $transaction): array => [
                    'name' => (string) $transaction->name,
                    'budget' => (string) $transaction->budget_name,
                    'spent' => (int) $transaction->amount,
                    'receipt' => $transaction->receipt_document_id === null ? 'Missing' : 'Uploaded',
                    'status' => (string) $transaction->status,
                ])
                ->all(),
        ];
    }
}
