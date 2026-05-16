<?php

declare(strict_types=1);

namespace App\DTOs\Task;

use App\Support\ValueObjects\Progress;

final readonly class TaskBoardSummaryData
{
    public function __construct(
        public int $totalTasks,
        public int $openTasks,
        public int $doneTasks,
        public int $blockedTasks,
        public int $overdueTasks,
        public int $dueSoonTasks,
        public Progress $completionProgress,
    ) {}

    /**
     * @return array{
     *     totalTasks: int,
     *     openTasks: int,
     *     doneTasks: int,
     *     blockedTasks: int,
     *     overdueTasks: int,
     *     dueSoonTasks: int,
     *     completionProgress: int
     * }
     */
    public function toArray(): array
    {
        return [
            'totalTasks' => $this->totalTasks,
            'openTasks' => $this->openTasks,
            'doneTasks' => $this->doneTasks,
            'blockedTasks' => $this->blockedTasks,
            'overdueTasks' => $this->overdueTasks,
            'dueSoonTasks' => $this->dueSoonTasks,
            'completionProgress' => $this->completionProgress->percentage,
        ];
    }
}
