<?php

declare(strict_types=1);

namespace App\Domain\Letter;

enum LetterStatus: string
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case Signed = 'signed';
    case Sent = 'sent';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Submitted => 'Menunggu Tanda Tangan',
            self::Signed => 'Ditandatangani',
            self::Sent => 'Terkirim',
        };
    }
}
