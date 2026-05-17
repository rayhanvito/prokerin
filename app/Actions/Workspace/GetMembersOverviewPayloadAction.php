<?php

declare(strict_types=1);

namespace App\Actions\Workspace;

use App\Domain\Membership\InvitationStatus;
use App\Support\Roles;
use Illuminate\Support\Facades\DB;

final class GetMembersOverviewPayloadAction
{
    /**
     * @return array{canManage: bool, metrics: array<int, array{label: string, value: string, note: string}>, members: array<int, array{id: int, name: string, email: string, role: string, joinedAt: string|null, status: string}>, roleBreakdown: array<int, array{role: string, total: int}>}
     */
    public function execute(int $actorUserId, ?int $preferredOrganizationId = null): array
    {
        $context = app(GetOrganizationPeriodsPayloadAction::class)->execute($actorUserId, $preferredOrganizationId);
        $organizationId = $context['organization']['id'] ?? null;

        if ($organizationId === null) {
            return [
                'canManage' => false,
                'metrics' => [],
                'members' => [],
                'roleBreakdown' => [],
            ];
        }

        $members = DB::table('organization_members')
            ->join('users', 'users.id', '=', 'organization_members.user_id')
            ->where('organization_members.organization_id', $organizationId)
            ->orderByRaw("case organization_members.role when 'organization_owner' then 0 when 'organization_admin' then 1 else 2 end")
            ->orderBy('users.name')
            ->get([
                'organization_members.id',
                'organization_members.role',
                'organization_members.joined_at',
                'users.name',
                'users.email',
            ]);
        $pendingInvitations = DB::table('organization_invitations')
            ->where('organization_id', $organizationId)
            ->where('status', InvitationStatus::Pending->value)
            ->count();
        $actorRole = DB::table('organization_members')
            ->where('organization_id', $organizationId)
            ->where('user_id', $actorUserId)
            ->value('role');
        $newMembers = $members->filter(static fn (object $member): bool => $member->joined_at !== null && now()->subDays(30)->lessThanOrEqualTo($member->joined_at))->count();
        $roleBreakdown = $members
            ->groupBy('role')
            ->map(static fn ($items, string $role): array => [
                'role' => $role,
                'total' => $items->count(),
            ])
            ->values()
            ->all();

        return [
            'canManage' => $actorRole === Roles::ORGANIZATION_OWNER,
            'metrics' => [
                ['label' => 'Members', 'value' => (string) $members->count(), 'note' => 'Aktif di organisasi ini'],
                ['label' => 'Invites', 'value' => (string) $pendingInvitations, 'note' => 'Belum diterima'],
                ['label' => 'Roles', 'value' => (string) count($roleBreakdown), 'note' => 'Role terpakai'],
                ['label' => 'New', 'value' => (string) $newMembers, 'note' => '30 hari terakhir'],
            ],
            'members' => $members
                ->map(static fn (object $member): array => [
                    'id' => (int) $member->id,
                    'name' => (string) $member->name,
                    'email' => (string) $member->email,
                    'role' => (string) $member->role,
                    'joinedAt' => $member->joined_at === null ? null : (string) $member->joined_at,
                    'status' => 'Active',
                ])
                ->all(),
            'roleBreakdown' => $roleBreakdown,
        ];
    }
}
