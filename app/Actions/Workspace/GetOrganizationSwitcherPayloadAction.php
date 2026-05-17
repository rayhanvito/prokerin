<?php

declare(strict_types=1);

namespace App\Actions\Workspace;

use Illuminate\Support\Facades\DB;

final class GetOrganizationSwitcherPayloadAction
{
    /**
     * @return array{activeOrganizationId: int|null, organizations: array<int, array{id: int, name: string, role: string, period: string, memberCount: int, active: bool}>}
     */
    public function execute(int $actorUserId, ?int $preferredOrganizationId = null): array
    {
        $organizations = DB::table('organization_members')
            ->join('organizations', 'organizations.id', '=', 'organization_members.organization_id')
            ->leftJoin('organization_periods', function ($join): void {
                $join->on('organization_periods.organization_id', '=', 'organizations.id')
                    ->where('organization_periods.is_active', true);
            })
            ->where('organization_members.user_id', $actorUserId)
            ->orderBy('organization_members.id')
            ->get([
                'organizations.id',
                'organizations.name',
                'organization_members.role',
                'organization_periods.name as period_name',
            ]);

        $ids = $organizations->pluck('id')->map(static fn (mixed $id): int => (int) $id);
        $activeOrganizationId = $preferredOrganizationId !== null && $ids->contains($preferredOrganizationId)
            ? $preferredOrganizationId
            : ($ids->first() ?: null);

        $memberCounts = DB::table('organization_members')
            ->whereIn('organization_id', $ids)
            ->select('organization_id', DB::raw('count(*) as total'))
            ->groupBy('organization_id')
            ->pluck('total', 'organization_id');

        return [
            'activeOrganizationId' => $activeOrganizationId,
            'organizations' => $organizations
                ->map(static fn (object $organization): array => [
                    'id' => (int) $organization->id,
                    'name' => (string) $organization->name,
                    'role' => (string) $organization->role,
                    'period' => (string) ($organization->period_name ?? '-'),
                    'memberCount' => (int) ($memberCounts[(int) $organization->id] ?? 0),
                    'active' => $activeOrganizationId === (int) $organization->id,
                ])
                ->all(),
        ];
    }
}
