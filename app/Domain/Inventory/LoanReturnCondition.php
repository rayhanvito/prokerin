<?php

declare(strict_types=1);

namespace App\Domain\Inventory;

enum LoanReturnCondition: string
{
    case Same = 'same';
    case Damaged = 'damaged';
    case Lost = 'lost';

    public function label(): string
    {
        return match ($this) {
            self::Same => 'Sama seperti saat dipinjam',
            self::Damaged => 'Rusak saat kembali',
            self::Lost => 'Hilang',
        };
    }
}
