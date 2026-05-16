<?php

declare(strict_types=1);

namespace App\Domain\Membership;

enum InvitationDecision: string
{
    case Accept = 'accept';
    case Revoke = 'revoke';
    case Expire = 'expire';
}
