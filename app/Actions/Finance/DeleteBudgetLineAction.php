<?php

declare(strict_types=1);

namespace App\Actions\Finance;

use App\Domain\Finance\BudgetStatus;
use App\Support\Roles;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class DeleteBudgetLineAction
{
    public function execute(int $actorUserId, int $budgetLineId): void
    {
        $budgetLine = DB::table('budget_lines')
            ->join('projects', 'projects.id', '=', 'budget_lines.project_id')
            ->where('budget_lines.id', $budgetLineId)
            ->first([
                'budget_lines.id',
                'budget_lines.status',
                'projects.organization_id',
            ]);

        if ($budgetLine === null) {
            throw new NotFoundHttpException('Budget line tidak ditemukan.');
        }

        $this->guardManager($actorUserId, (int) $budgetLine->organization_id);
        $this->guardDeletableStatus((string) $budgetLine->status);

        $hasTransactions = DB::table('budget_transactions')
            ->where('budget_line_id', $budgetLine->id)
            ->exists();

        if ($hasTransactions) {
            throw new AuthorizationException('Budget line dengan transaksi tidak dapat dihapus.');
        }

        DB::table('budget_lines')->where('id', $budgetLine->id)->delete();
    }

    private function guardManager(int $actorUserId, int $organizationId): void
    {
        $role = (string) DB::table('organization_members')
            ->where('user_id', $actorUserId)
            ->where('organization_id', $organizationId)
            ->value('role');

        if (! in_array($role, Roles::FINANCE_MANAGERS, true)) {
            throw new AuthorizationException('Hanya owner, admin, atau bendahara yang dapat menghapus budget line.');
        }
    }

    private function guardDeletableStatus(string $status): void
    {
        $deletable = [BudgetStatus::Draft->value, BudgetStatus::Rejected->value];

        if (! in_array($status, $deletable, true)) {
            throw new AuthorizationException('Hanya budget line dengan status draft atau rejected yang dapat dihapus.');
        }
    }
}
