<?php

declare(strict_types=1);

namespace App\Actions\Organization;

use App\Actions\Workspace\GetActiveOrganizationContextAction;
use App\Support\Roles;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class UpdateOrganizationPeriodAction
{
    public function __construct(
        private readonly GetActiveOrganizationContextAction $activeOrganizationContext,
    ) {}

    /**
     * @param  array{name: string, starts_at: string, ends_at: string, is_active?: bool}  $data
     *
     * @throws AuthorizationException
     */
    public function execute(int $actorUserId, int $periodId, array $data): void
    {
        $period = DB::table('organization_periods')
            ->where('id', $periodId)
            ->first(['id', 'organization_id']);

        if ($period === null) {
            throw new NotFoundHttpException('Organization period was not found.');
        }

        $context = $this->activeOrganizationContext->execute($actorUserId, (int) $period->organization_id);

        if ($context->organizationId !== (int) $period->organization_id) {
            throw new NotFoundHttpException('Organization period was not found.');
        }

        if (! in_array($context->role, Roles::ORGANIZATION_MANAGERS, true)) {
            throw new AuthorizationException('You are not allowed to manage organization periods.');
        }

        DB::transaction(function () use ($context, $periodId, $data): void {
            $now = now();
            $isActive = (bool) ($data['is_active'] ?? false);

            if ($isActive) {
                DB::table('organization_periods')
                    ->where('organization_id', $context->organizationId)
                    ->where('id', '!=', $periodId)
                    ->update([
                        'is_active' => false,
                        'updated_at' => $now,
                    ]);
            }

            DB::table('organization_periods')
                ->where('id', $periodId)
                ->where('organization_id', $context->organizationId)
                ->update([
                    'name' => $data['name'],
                    'starts_at' => $data['starts_at'],
                    'ends_at' => $data['ends_at'],
                    'is_active' => $isActive,
                    'updated_at' => $now,
                ]);
        });
    }
}
