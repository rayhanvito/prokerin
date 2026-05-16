<?php

declare(strict_types=1);

namespace App\Domain\Report;

enum LpjApprovalDecision: string
{
    case Approve = 'approve';
    case RequestChanges = 'request_changes';
}
