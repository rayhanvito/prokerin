<?php

declare(strict_types=1);

namespace App\Actions\Project;

use App\Domain\Project\ProjectStatus;
use DomainException;

final class TransitionProjectStatusAction
{
    /**
     * @var array<string, array<int, ProjectStatus>>
     */
    private const ALLOWED_TRANSITIONS = [
        'draft' => [ProjectStatus::ProposalReview],
        'proposal_review' => [ProjectStatus::Draft, ProjectStatus::RabApproval],
        'rab_approval' => [ProjectStatus::ProposalReview, ProjectStatus::ReadyToExecute],
        'ready_to_execute' => [ProjectStatus::Running],
        'running' => [ProjectStatus::LpjReview],
        'lpj_review' => [ProjectStatus::Running, ProjectStatus::Completed],
        'completed' => [ProjectStatus::Archived],
        'archived' => [],
    ];

    public function execute(ProjectStatus $currentStatus, ProjectStatus $targetStatus): ProjectStatus
    {
        if ($currentStatus === $targetStatus) {
            return $currentStatus;
        }

        if (! $this->canTransition($currentStatus, $targetStatus)) {
            throw new DomainException(sprintf(
                'Cannot transition project status from %s to %s.',
                $currentStatus->value,
                $targetStatus->value,
            ));
        }

        return $targetStatus;
    }

    public function canTransition(ProjectStatus $currentStatus, ProjectStatus $targetStatus): bool
    {
        return in_array($targetStatus, self::ALLOWED_TRANSITIONS[$currentStatus->value], true);
    }

    /**
     * @return array<int, ProjectStatus>
     */
    public function nextStatuses(ProjectStatus $currentStatus): array
    {
        return self::ALLOWED_TRANSITIONS[$currentStatus->value];
    }
}
