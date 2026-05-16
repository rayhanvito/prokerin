<?php

declare(strict_types=1);

namespace App\Domain\Task;

enum TaskStatus: string
{
    case Backlog = 'backlog';
    case Todo = 'todo';
    case InProgress = 'in_progress';
    case Review = 'review';
    case Done = 'done';
    case Blocked = 'blocked';

    public function label(): string
    {
        return match ($this) {
            self::Backlog => 'Backlog',
            self::Todo => 'Todo',
            self::InProgress => 'In Progress',
            self::Review => 'Review',
            self::Done => 'Done',
            self::Blocked => 'Blocked',
        };
    }

    public function countsAsFinished(): bool
    {
        return $this === self::Done;
    }
}
