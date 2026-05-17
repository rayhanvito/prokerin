<?php

declare(strict_types=1);

namespace App\Actions\Workspace;

use App\Support\Roles;
use Illuminate\Support\Facades\DB;

final class GetOrganizationPeriodsPayloadAction
{
    /**
     * @return array{canManage: bool, organization: array{id: int, name: string}|null, periods: array<int, array{id: int, period: string, start: string, end: string, owner: string, status: string}>}
     */
    public function execute(int $actorUserId, ?int $preferredOrganizationId = null): array
    {
        $membership = DB::table('organization_members')
            ->join('organizations', 'organizations.id', '=', 'organization_members.organization_id')
            ->where('organization_members.user_id', $actorUserId)
            ->when($preferredOrganizationId !== null, static function ($query) use ($preferredOrganizationId): void {
                $query->where('organization_members.organization_id', $preferredOrganizationId);
            })
            ->orderBy('organization_members.id')
            ->first([
                'organization_members.organization_id',
                'organization_members.role',
                'organizations.name',
            ]);

        if ($membership === null && $preferredOrganizationId !== null) {
            $membership = DB::table('organization_members')
                ->join('organizations', 'organizations.id', '=', 'organization_members.organization_id')
                ->where('organization_members.user_id', $actorUserId)
                ->orderBy('organization_members.id')
                ->first([
                    'organization_members.organization_id',
                    'organization_members.role',
                    'organizations.name',
                ]);
        }

        if ($membership === null) {
            return [
                'canManage' => false,
                'organization' => null,
                'periods' => [],
            ];
        }

        $organizationId = (int) $membership->organization_id;

        return [
            'canManage' => in_array((string) $membership->role, Roles::ORGANIZATION_MANAGERS, true),
            'organization' => [
                'id' => $organizationId,
                'name' => (string) $membership->name,
            ],
            'periods' => DB::table('organization_periods')
                ->where('organization_id', $organizationId)
                ->orderByDesc('starts_at')
                ->get(['id', 'name', 'starts_at', 'ends_at', 'is_active'])
                ->map(static fn (object $period): array => [
                    'id' => (int) $period->id,
                    'period' => (string) $period->name,
                    'start' => (string) $period->starts_at,
                    'end' => (string) $period->ends_at,
                    'owner' => (string) $membership->name,
                    'status' => (bool) $period->is_active ? 'Active' : 'Archived',
                ])
                ->all(),
        ];
    }
}
