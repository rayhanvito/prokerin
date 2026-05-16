<?php

declare(strict_types=1);

namespace App\Domain\Finance;

enum BudgetStatus: string
{
    case Draft = 'draft';
    case Review = 'review';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Realized = 'realized';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Review => 'Review',
            self::Approved => 'Approved',
            self::Rejected => 'Rejected',
            self::Realized => 'Realized',
        };
    }

    public function isEditable(): bool
    {
        return in_array($this, [self::Draft, self::Rejected], true);
    }
}
