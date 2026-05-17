<?php

declare(strict_types=1);

namespace App\Actions\Organization;

use App\Actions\Workspace\GetActiveOrganizationContextAction;
use App\Support\Roles;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

final class StoreOrganizationPeriodAction
{
    public function __construct(
        private readonly GetActiveOrganizationContextAction $activeOrganizationContext,
    ) {}

    /**
     * @param  array{name: string, starts_at: string, ends_at: string, is_active?: bool}  $data
     *
     * @throws AuthorizationException
     */
    public function execute(int $actorUserId, array $data): int
    {
        $activeOrganizationId = session('active_organization_id');
        $context = $this->activeOrganizationContext->execute(
            $actorUserId,
            is_numeric($activeOrganizationId) ? (int) $activeOrganizationId : null,
        );

        if (! in_array($context->role, Roles::ORGANIZATION_MANAGERS, true)) {
            throw new AuthorizationException('You are not allowed to manage organization periods.');
        }

        return DB::transaction(function () use ($context, $data): int {
            $now = now();
            $isActive = (bool) ($data['is_active'] ?? false);

            if ($isActive) {
                DB::table('organization_periods')
                    ->where('organization_id', $context->organizationId)
                    ->update([
                        'is_active' => false,
                        'updated_at' => $now,
                    ]);
            }

            return (int) DB::table('organization_periods')->insertGetId([
                'organization_id' => $context->organizationId,
                'name' => $data['name'],
                'starts_at' => $data['starts_at'],
                'ends_at' => $data['ends_at'],
                'is_active' => $isActive,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        });
    }
}
