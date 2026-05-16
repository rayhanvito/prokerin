<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Actions\Task\CalculateTaskBoardSummaryAction;
use App\Domain\Task\TaskStatus;
use App\DTOs\Task\TaskLineData;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class CalculateTaskBoardSummaryActionTest extends TestCase
{
    public function test_it_calculates_task_board_summary(): void
    {
        $now = new DateTimeImmutable('2026-05-16 10:00:00');

        $summary = (new CalculateTaskBoardSummaryAction)->execute([
            $this->task(TaskStatus::Done, '2026-05-15 10:00:00'),
            $this->task(TaskStatus::InProgress, '2026-05-15 09:00:00'),
            $this->task(TaskStatus::Blocked, '2026-05-18 09:00:00'),
            $this->task(TaskStatus::Review, '2026-05-26 09:00:00'),
        ], $now);

        $this->assertSame(4, $summary->totalTasks);
        $this->assertSame(3, $summary->openTasks);
        $this->assertSame(1, $summary->doneTasks);
        $this->assertSame(1, $summary->blockedTasks);
        $this->assertSame(1, $summary->overdueTasks);
        $this->assertSame(1, $summary->dueSoonTasks);
        $this->assertSame(25, $summary->completionProgress->percentage);
    }

    public function test_empty_task_board_summary_is_zeroed(): void
    {
        $summary = (new CalculateTaskBoardSummaryAction)->execute(
            [],
            new DateTimeImmutable('2026-05-16 10:00:00'),
        );

        $this->assertSame(0, $summary->totalTasks);
        $this->assertSame(0, $summary->openTasks);
        $this->assertSame(0, $summary->completionProgress->percentage);
    }

    private function task(TaskStatus $status, string $dueAt): TaskLineData
    {
        return new TaskLineData(
            title: 'Finalisasi rundown',
            projectName: 'Seminar Karier Digital',
            picName: 'Nadia Putri',
            status: $status,
            dueAt: new DateTimeImmutable($dueAt),
        );
    }
}
