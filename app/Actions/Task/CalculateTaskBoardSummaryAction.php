<?php

declare(strict_types=1);

namespace App\Actions\Task;

use App\DTOs\Task\TaskBoardSummaryData;
use App\DTOs\Task\TaskLineData;
use App\Support\ValueObjects\Progress;
use DateTimeImmutable;

final class CalculateTaskBoardSummaryAction
{
    /**
     * @param  array<int, TaskLineData>  $tasks
     */
    public function execute(array $tasks, DateTimeImmutable $now, int $dueSoonDays = 7): TaskBoardSummaryData
    {
        $doneTasks = 0;
        $openTasks = 0;
        $blockedTasks = 0;
        $overdueTasks = 0;
        $dueSoonTasks = 0;
        $dueSoonLimit = $now->modify(sprintf('+%d days', $dueSoonDays));

        foreach ($tasks as $task) {
            if ($task->status->countsAsFinished()) {
                $doneTasks++;

                continue;
            }

            $openTasks++;

            if ($task->isBlocked()) {
                $blockedTasks++;
            }

            if ($task->dueAt < $now) {
                $overdueTasks++;
            }

            if ($task->dueAt >= $now && $task->dueAt <= $dueSoonLimit) {
                $dueSoonTasks++;
            }
        }

        return new TaskBoardSummaryData(
            totalTasks: count($tasks),
            openTasks: $openTasks,
            doneTasks: $doneTasks,
            blockedTasks: $blockedTasks,
            overdueTasks: $overdueTasks,
            dueSoonTasks: $dueSoonTasks,
            completionProgress: Progress::fromCompletedItems($doneTasks, count($tasks)),
        );
    }
}
