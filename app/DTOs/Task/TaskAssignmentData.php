<?php

declare(strict_types=1);

namespace App\DTOs\Task;

use App\Domain\Project\ProjectRole;

final readonly class TaskAssignmentData
{
    public function __construct(
        public string $taskTitle,
        public string $projectName,
        public string $memberName,
        public ProjectRole $projectRole,
    ) {}
}
