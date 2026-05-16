<?php

declare(strict_types=1);

namespace App\DTOs\Task;

use App\Domain\Task\TaskStatus;
use DateTimeImmutable;

final readonly class TaskLineData
{
    public function __construct(
        public string $title,
        public string $projectName,
        public string $picName,
        public TaskStatus $status,
        public DateTimeImmutable $dueAt,
    ) {}

    public function isOpen(): bool
    {
        return ! $this->status->countsAsFinished();
    }

    public function isBlocked(): bool
    {
        return $this->status === TaskStatus::Blocked;
    }
}
