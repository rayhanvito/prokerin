<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Actions\Membership\DecideInvitationAction;
use App\Domain\Membership\InvitationDecision;
use App\Domain\Membership\InvitationStatus;
use DomainException;
use PHPUnit\Framework\TestCase;

final class DecideInvitationActionTest extends TestCase
{
    public function test_it_accepts_pending_invitation(): void
    {
        $status = (new DecideInvitationAction)->execute(
            InvitationStatus::Pending,
            InvitationDecision::Accept,
        );

        $this->assertSame(InvitationStatus::Accepted, $status);
    }

    public function test_it_revokes_pending_invitation(): void
    {
        $status = (new DecideInvitationAction)->execute(
            InvitationStatus::Pending,
            InvitationDecision::Revoke,
        );

        $this->assertSame(InvitationStatus::Revoked, $status);
    }

    public function test_it_expires_pending_invitation(): void
    {
        $status = (new DecideInvitationAction)->execute(
            InvitationStatus::Pending,
            InvitationDecision::Expire,
        );

        $this->assertSame(InvitationStatus::Expired, $status);
    }

    public function test_it_rejects_change_for_closed_invitation(): void
    {
        $this->expectException(DomainException::class);

        (new DecideInvitationAction)->execute(
            InvitationStatus::Accepted,
            InvitationDecision::Revoke,
        );
    }
}
