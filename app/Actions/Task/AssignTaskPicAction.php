<?php

declare(strict_types=1);

namespace App\Actions\Task;

use App\Domain\Project\ProjectRole;
use App\DTOs\Task\TaskAssignmentData;
use DomainException;

final class AssignTaskPicAction
{
    public function execute(TaskAssignmentData $assignment): TaskAssignmentData
    {
        if ($assignment->projectRole === ProjectRole::Viewer) {
            throw new DomainException('Project viewers cannot be assigned as task PIC.');
        }

        return $assignment;
    }
}
