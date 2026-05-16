<?php

declare(strict_types=1);

namespace App\Domain\Meeting;

enum AttendanceStatus: string
{
    case Invited = 'invited';
    case Present = 'present';
    case Absent = 'absent';
    case Excused = 'excused';

    public function label(): string
    {
        return match ($this) {
            self::Invited => 'Invited',
            self::Present => 'Present',
            self::Absent => 'Absent',
            self::Excused => 'Excused',
        };
    }
}
