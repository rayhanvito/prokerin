<?php

declare(strict_types=1);

namespace App\Domain\DocumentExport;

enum ExportDocumentType: string
{
    case Proposal = 'proposal';
    case Lpj = 'lpj';
    case Handover = 'handover';
    case EventRegistration = 'event_registration';

    public function label(): string
    {
        return match ($this) {
            self::Proposal => 'Proposal',
            self::Lpj => 'LPJ',
            self::Handover => 'Handover',
            self::EventRegistration => 'Registrasi Event',
        };
    }
}
