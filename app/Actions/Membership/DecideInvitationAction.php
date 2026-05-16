<?php

declare(strict_types=1);

namespace App\Actions\Membership;

use App\Domain\Membership\InvitationDecision;
use App\Domain\Membership\InvitationStatus;
use DomainException;

final class DecideInvitationAction
{
    public function execute(InvitationStatus $currentStatus, InvitationDecision $decision): InvitationStatus
    {
        if (! $currentStatus->isOpen()) {
            throw new DomainException('Only pending invitations can be changed.');
        }

        return match ($decision) {
            InvitationDecision::Accept => InvitationStatus::Accepted,
            InvitationDecision::Revoke => InvitationStatus::Revoked,
            InvitationDecision::Expire => InvitationStatus::Expired,
        };
    }
}
