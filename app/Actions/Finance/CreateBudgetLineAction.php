<?php

declare(strict_types=1);

namespace App\Actions\Finance;

use App\Domain\Finance\BudgetStatus;
use App\Support\Roles;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class CreateBudgetLineAction
{
    /**
     * @param  array{
     *     project_id: int,
     *     name: string,
     *     category: string,
     *     planned_amount: int,
     * }  $input
     */
    public function execute(int $actorUserId, array $input): int
    {
        $project = DB::table('projects')
            ->where('id', $input['project_id'])
            ->first(['id', 'organization_id']);

        if ($project === null) {
            throw new NotFoundHttpException('Project tidak ditemukan.');
        }

        $this->guardManager($actorUserId, (int) $project->organization_id);

        $now = now();

        return (int) DB::table('budget_lines')->insertGetId([
            'project_id' => (int) $project->id,
            'name' => trim((string) $input['name']),
            'category' => trim((string) $input['category']),
            'planned_amount' => (int) $input['planned_amount'],
            'realized_amount' => 0,
            'status' => BudgetStatus::Draft->value,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    private function guardManager(int $actorUserId, int $organizationId): void
    {
        $role = (string) DB::table('organization_members')
            ->where('user_id', $actorUserId)
            ->where('organization_id', $organizationId)
            ->value('role');

        if (! in_array($role, Roles::FINANCE_MANAGERS, true)) {
            throw new AuthorizationException('Hanya owner, admin, atau bendahara yang dapat membuat budget line.');
        }
    }
}
