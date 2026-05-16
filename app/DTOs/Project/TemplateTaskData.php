<?php

declare(strict_types=1);

namespace App\DTOs\Project;

use App\Domain\Task\TaskStatus;

final readonly class TemplateTaskData
{
    public function __construct(
        public string $title,
        public string $division,
        public int $dueOffsetDays,
        public TaskStatus $status = TaskStatus::Backlog,
    ) {}

    /**
     * @return array{title: string, division: string, dueOffsetDays: int, status: string}
     */
    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'division' => $this->division,
            'dueOffsetDays' => $this->dueOffsetDays,
            'status' => $this->status->value,
        ];
    }
}
