<?php

declare(strict_types=1);

namespace App\Domain\Finance;

enum BudgetApprovalDecision: string
{
    case Approve = 'approve';
    case Reject = 'reject';
}
