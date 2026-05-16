<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Domain\Finance\BudgetStatus;
use App\Domain\Membership\InvitationStatus;
use App\Domain\Organization\OrganizationRole;
use App\Domain\Project\ProjectRole;
use App\Domain\Project\ProjectStatus;
use App\Domain\Task\TaskStatus;
use PHPUnit\Framework\TestCase;

final class DomainStatusTest extends TestCase
{
    public function test_organization_owner_can_manage_organization(): void
    {
        $this->assertTrue(OrganizationRole::Owner->canManageOrganization());
        $this->assertFalse(OrganizationRole::Viewer->canManageOrganization());
    }

    public function test_invitation_open_status_is_pending_only(): void
    {
        $this->assertTrue(InvitationStatus::Pending->isOpen());
        $this->assertFalse(InvitationStatus::Accepted->isOpen());
    }

    public function test_project_terminal_statuses_are_explicit(): void
    {
        $this->assertTrue(ProjectStatus::Completed->isTerminal());
        $this->assertTrue(ProjectStatus::Archived->isTerminal());
        $this->assertFalse(ProjectStatus::Running->isTerminal());
    }

    public function test_project_leads_and_division_coordinators_can_manage_project(): void
    {
        $this->assertTrue(ProjectRole::ProjectLead->canManageProject());
        $this->assertTrue(ProjectRole::DivisionCoordinator->canManageProject());
        $this->assertFalse(ProjectRole::Viewer->canManageProject());
    }

    public function test_task_done_counts_as_finished(): void
    {
        $this->assertTrue(TaskStatus::Done->countsAsFinished());
        $this->assertFalse(TaskStatus::Review->countsAsFinished());
    }

    public function test_budget_editable_statuses_are_draft_and_rejected(): void
    {
        $this->assertTrue(BudgetStatus::Draft->isEditable());
        $this->assertTrue(BudgetStatus::Rejected->isEditable());
        $this->assertFalse(BudgetStatus::Approved->isEditable());
    }
}
