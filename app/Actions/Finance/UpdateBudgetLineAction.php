<?php

declare(strict_types=1);

namespace App\Actions\Finance;

use App\Domain\Finance\BudgetStatus;
use App\Support\Roles;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class UpdateBudgetLineAction
{
    /**
     * @param  array{
     *     name?: string,
     *     category?: string,
     *     planned_amount?: int,
     * }  $input
     */
    public function execute(int $actorUserId, int $budgetLineId, array $input): void
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
        $this->guardEditableStatus((string) $budgetLine->status);

        $patch = [];

        foreach (['name', 'category'] as $field) {
            if (array_key_exists($field, $input) && is_string($input[$field])) {
                $patch[$field] = trim($input[$field]);
            }
        }

        if (array_key_exists('planned_amount', $input)) {
            $patch['planned_amount'] = (int) $input['planned_amount'];
        }

        if ($patch === []) {
            return;
        }

        $patch['updated_at'] = now();

        DB::table('budget_lines')
            ->where('id', $budgetLine->id)
            ->update($patch);
    }

    private function guardManager(int $actorUserId, int $organizationId): void
    {
        $role = (string) DB::table('organization_members')
            ->where('user_id', $actorUserId)
            ->where('organization_id', $organizationId)
            ->value('role');

        if (! in_array($role, Roles::FINANCE_MANAGERS, true)) {
            throw new AuthorizationException('Hanya owner, admin, atau bendahara yang dapat mengubah budget line.');
        }
    }

    private function guardEditableStatus(string $status): void
    {
        $editable = [BudgetStatus::Draft->value, BudgetStatus::Review->value, BudgetStatus::Rejected->value];

        if (! in_array($status, $editable, true)) {
            throw new AuthorizationException('Budget line yang sudah approved/realized tidak dapat diubah.');
        }
    }
}
