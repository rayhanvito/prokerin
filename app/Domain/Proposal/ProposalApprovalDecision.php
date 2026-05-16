<?php

declare(strict_types=1);

namespace App\Domain\Proposal;

enum ProposalApprovalDecision: string
{
    case Approve = 'approve';
    case RequestChanges = 'request_changes';
}
