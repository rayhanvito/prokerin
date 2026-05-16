<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Actions\Task\AssignTaskPicAction;
use App\Domain\Project\ProjectRole;
use App\DTOs\Task\TaskAssignmentData;
use DomainException;
use PHPUnit\Framework\TestCase;

final class AssignTaskPicActionTest extends TestCase
{
    public function test_it_allows_committee_member_as_task_pic(): void
    {
        $assignment = (new AssignTaskPicAction)->execute(
            new TaskAssignmentData(
                taskTitle: 'Finalisasi rundown',
                projectName: 'Seminar Karier',
                memberName: 'Nadia Putri',
                projectRole: ProjectRole::CommitteeMember,
            ),
        );

        $this->assertSame('Nadia Putri', $assignment->memberName);
    }

    public function test_it_allows_project_lead_as_task_pic(): void
    {
        $assignment = (new AssignTaskPicAction)->execute(
            new TaskAssignmentData(
                taskTitle: 'Timeline setup',
                projectName: 'Makrab 2026',
                memberName: 'Dimas Aji',
                projectRole: ProjectRole::ProjectLead,
            ),
        );

        $this->assertSame(ProjectRole::ProjectLead, $assignment->projectRole);
    }

    public function test_it_rejects_viewer_as_task_pic(): void
    {
        $this->expectException(DomainException::class);

        (new AssignTaskPicAction)->execute(
            new TaskAssignmentData(
                taskTitle: 'RAB approval',
                projectName: 'Workshop UI/UX',
                memberName: 'Viewer User',
                projectRole: ProjectRole::Viewer,
            ),
        );
    }
}
