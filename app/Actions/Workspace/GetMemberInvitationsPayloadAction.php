<?php

declare(strict_types=1);

namespace App\Actions\Workspace;

use Illuminate\Support\Facades\DB;

final class GetMemberInvitationsPayloadAction
{
    /**
     * @return array{canManage: bool, invitations: array<int, array{id: int, email: string, role: string, organization: string, sent: string, expiresAt: string|null, status: string}>}
     */
    public function execute(int $actorUserId, ?int $preferredOrganizationId = null): array
    {
        $context = app(GetOrganizationPeriodsPayloadAction::class)->execute($actorUserId, $preferredOrganizationId);
        $organizationId = $context['organization']['id'] ?? null;

        if ($organizationId === null) {
            return [
                'canManage' => false,
                'invitations' => [],
            ];
        }

        return [
            'canManage' => $context['canManage'],
            'invitations' => DB::table('organization_invitations')
                ->join('organizations', 'organizations.id', '=', 'organization_invitations.organization_id')
                ->where('organization_invitations.organization_id', $organizationId)
                ->orderByDesc('organization_invitations.created_at')
                ->get([
                    'organization_invitations.id',
                    'organization_invitations.email',
                    'organization_invitations.role',
                    'organization_invitations.status',
                    'organization_invitations.expires_at',
                    'organization_invitations.created_at',
                    'organizations.name as organization_name',
                ])
                ->map(static fn (object $invitation): array => [
                    'id' => (int) $invitation->id,
                    'email' => (string) $invitation->email,
                    'role' => (string) $invitation->role,
                    'organization' => (string) $invitation->organization_name,
                    'sent' => (string) $invitation->created_at,
                    'expiresAt' => $invitation->expires_at === null ? null : (string) $invitation->expires_at,
                    'status' => ucfirst((string) $invitation->status),
                ])
                ->all(),
        ];
    }
}
