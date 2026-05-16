<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Actions\Project\TransitionProjectStatusAction;
use App\Domain\Project\ProjectStatus;
use DomainException;
use PHPUnit\Framework\TestCase;

final class TransitionProjectStatusActionTest extends TestCase
{
    public function test_it_allows_forward_project_status_transition(): void
    {
        $status = (new TransitionProjectStatusAction)->execute(
            ProjectStatus::Draft,
            ProjectStatus::ProposalReview,
        );

        $this->assertSame(ProjectStatus::ProposalReview, $status);
    }

    public function test_it_allows_explicit_revision_transition(): void
    {
        $status = (new TransitionProjectStatusAction)->execute(
            ProjectStatus::LpjReview,
            ProjectStatus::Running,
        );

        $this->assertSame(ProjectStatus::Running, $status);
    }

    public function test_it_rejects_skipped_project_status_transition(): void
    {
        $this->expectException(DomainException::class);

        (new TransitionProjectStatusAction)->execute(
            ProjectStatus::Draft,
            ProjectStatus::Running,
        );
    }

    public function test_it_exposes_next_statuses_for_status_flow_ui(): void
    {
        $nextStatuses = (new TransitionProjectStatusAction)->nextStatuses(ProjectStatus::RabApproval);

        $this->assertSame(
            [ProjectStatus::ProposalReview, ProjectStatus::ReadyToExecute],
            $nextStatuses,
        );
    }

    public function test_archived_project_has_no_next_status(): void
    {
        $nextStatuses = (new TransitionProjectStatusAction)->nextStatuses(ProjectStatus::Archived);

        $this->assertSame([], $nextStatuses);
    }
}
