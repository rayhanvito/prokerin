<?php

declare(strict_types=1);

namespace App\Domain\DocumentExport;

enum ExportDocumentType: string
{
    case Proposal = 'proposal';
    case Lpj = 'lpj';
    case Handover = 'handover';

    public function label(): string
    {
        return match ($this) {
            self::Proposal => 'Proposal',
            self::Lpj => 'LPJ',
            self::Handover => 'Handover',
        };
    }
}
