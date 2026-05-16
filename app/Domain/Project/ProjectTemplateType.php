<?php

declare(strict_types=1);

namespace App\Domain\Project;

enum ProjectTemplateType: string
{
    case Seminar = 'seminar';
    case Workshop = 'workshop';
    case Competition = 'competition';
    case Makrab = 'makrab';

    public function label(): string
    {
        return match ($this) {
            self::Seminar => 'Seminar',
            self::Workshop => 'Workshop',
            self::Competition => 'Lomba',
            self::Makrab => 'Makrab',
        };
    }
}
