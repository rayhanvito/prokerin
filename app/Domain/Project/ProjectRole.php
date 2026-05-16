<?php

declare(strict_types=1);

namespace App\Domain\Project;

enum ProjectRole: string
{
    case ProjectLead = 'project_lead';
    case DivisionCoordinator = 'division_coordinator';
    case CommitteeMember = 'committee_member';
    case Viewer = 'viewer';

    public function label(): string
    {
        return match ($this) {
            self::ProjectLead => 'Project Lead',
            self::DivisionCoordinator => 'Division Coordinator',
            self::CommitteeMember => 'Committee Member',
            self::Viewer => 'Viewer',
        };
    }

    public function canManageProject(): bool
    {
        return in_array($this, [self::ProjectLead, self::DivisionCoordinator], true);
    }
}
