<?php

declare(strict_types=1);

namespace App\Actions\Membership;

use App\Domain\Membership\InvitationStatus;
use DomainException;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class RespondOrganizationInvitationAction
{
    public function accept(int $actorUserId, string $token): void
    {
        $this->respond($actorUserId, $token, InvitationStatus::Accepted);
    }

    public function decline(int $actorUserId, string $token): void
    {
        $this->respond($actorUserId, $token, InvitationStatus::Revoked);
    }

    private function respond(int $actorUserId, string $token, InvitationStatus $targetStatus): void
    {
        $invitation = DB::table('organization_invitations')
            ->where('token', $token)
            ->first([
                'id',
                'organization_id',
                'email',
                'role',
                'status',
                'expires_at',
            ]);

        if ($invitation === null) {
            throw new NotFoundHttpException('Invitation was not found.');
        }

        if ((string) $invitation->status !== InvitationStatus::Pending->value) {
            throw new DomainException('Invitation ini sudah tidak aktif.');
        }

        if ($invitation->expires_at !== null && now()->greaterThan($invitation->expires_at)) {
            DB::table('organization_invitations')
                ->where('id', $invitation->id)
                ->update([
                    'status' => InvitationStatus::Expired->value,
                    'updated_at' => now(),
                ]);

            throw new DomainException('Invitation ini sudah kedaluwarsa.');
        }

        $actorEmail = DB::table('users')->where('id', $actorUserId)->value('email');

        if (! is_string($actorEmail) || strtolower($actorEmail) !== strtolower((string) $invitation->email)) {
            throw new DomainException('Invitation ini hanya bisa dipakai oleh email tujuan.');
        }

        DB::transaction(function () use ($actorUserId, $invitation, $targetStatus): void {
            $now = now();

            if ($targetStatus === InvitationStatus::Accepted) {
                DB::table('organization_members')->updateOrInsert(
                    [
                        'organization_id' => (int) $invitation->organization_id,
                        'user_id' => $actorUserId,
                    ],
                    [
                        'role' => (string) $invitation->role,
                        'joined_at' => $now,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ],
                );
            }

            DB::table('organization_invitations')
                ->where('id', $invitation->id)
                ->update([
                    'status' => $targetStatus->value,
                    'accepted_by_user_id' => $targetStatus === InvitationStatus::Accepted ? $actorUserId : null,
                    'updated_at' => $now,
                ]);
        });
    }
}
