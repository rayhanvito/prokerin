<?php

declare(strict_types=1);

namespace App\Domain\Project;

enum ProjectStatus: string
{
    case Draft = 'draft';
    case ProposalReview = 'proposal_review';
    case RabApproval = 'rab_approval';
    case ReadyToExecute = 'ready_to_execute';
    case Running = 'running';
    case LpjReview = 'lpj_review';
    case Completed = 'completed';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::ProposalReview => 'Proposal Review',
            self::RabApproval => 'RAB Approval',
            self::ReadyToExecute => 'Ready to Execute',
            self::Running => 'Running',
            self::LpjReview => 'LPJ Review',
            self::Completed => 'Completed',
            self::Archived => 'Archived',
        };
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::Completed, self::Archived], true);
    }
}
