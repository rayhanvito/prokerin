<?php

declare(strict_types=1);

namespace App\Actions\Membership;

use App\Actions\Workspace\GetActiveOrganizationContextAction;
use App\Domain\Membership\InvitationStatus;
use App\Support\Roles;
use DomainException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class SendOrganizationInvitationAction
{
    public function __construct(
        private readonly GetActiveOrganizationContextAction $activeOrganizationContext,
    ) {}

    /**
     * @param  array{email: string, role: string}  $data
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
            throw new AuthorizationException('You are not allowed to invite organization members.');
        }

        $email = Str::lower($data['email']);
        $role = $data['role'];

        $isMember = DB::table('organization_members')
            ->join('users', 'users.id', '=', 'organization_members.user_id')
            ->where('organization_members.organization_id', $context->organizationId)
            ->where('users.email', $email)
            ->exists();

        if ($isMember) {
            throw new DomainException('Email ini sudah menjadi anggota organisasi.');
        }

        $hasPendingInvite = DB::table('organization_invitations')
            ->where('organization_id', $context->organizationId)
            ->where('email', $email)
            ->where('status', InvitationStatus::Pending->value)
            ->where(static function ($query): void {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->exists();

        if ($hasPendingInvite) {
            throw new DomainException('Invitation aktif untuk email ini sudah ada.');
        }

        $now = now();

        return (int) DB::table('organization_invitations')->insertGetId([
            'organization_id' => $context->organizationId,
            'email' => $email,
            'role' => $role,
            'status' => InvitationStatus::Pending->value,
            'token' => hash('sha256', Str::random(60)),
            'expires_at' => $now->copy()->addDays(7),
            'invited_by_user_id' => $actorUserId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
}
