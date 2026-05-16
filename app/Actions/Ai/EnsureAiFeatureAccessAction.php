<?php

declare(strict_types=1);

namespace App\Actions\Ai;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

final readonly class EnsureAiFeatureAccessAction
{
    /**
     * @return array{id: int, plan_tier: string, role: string}
     */
    public function execute(int $actorUserId, int $organizationId): array
    {
        $membership = DB::table('organization_members')
            ->join('organizations', 'organizations.id', '=', 'organization_members.organization_id')
            ->where('organization_members.user_id', $actorUserId)
            ->where('organizations.id', $organizationId)
            ->first([
                'organizations.id',
                'organizations.plan_tier',
                'organization_members.role',
            ]);

        if ($membership === null) {
            throw new AuthorizationException('Anda tidak memiliki akses organisasi ini.');
        }

        $planTier = (string) $membership->plan_tier;

        if (! in_array($planTier, ['pro', 'campus'], true)) {
            throw new AuthorizationException('Fitur AI tersedia untuk paket Pro atau Campus.');
        }

        return [
            'id' => (int) $membership->id,
            'plan_tier' => $planTier,
            'role' => (string) $membership->role,
        ];
    }
}
