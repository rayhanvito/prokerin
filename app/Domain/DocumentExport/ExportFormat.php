<?php

declare(strict_types=1);

namespace App\Domain\DocumentExport;

enum ExportFormat: string
{
    case Pdf = 'pdf';
    case Docx = 'docx';

    public function engine(): string
    {
        return match ($this) {
            self::Pdf => 'browsershot',
            self::Docx => 'phpword',
        };
    }
}
