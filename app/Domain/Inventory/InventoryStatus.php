<?php

declare(strict_types=1);

namespace App\Domain\Inventory;

enum InventoryStatus: string
{
    case Available = 'available';
    case Loaned = 'loaned';
    case Lost = 'lost';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Available => 'Tersedia',
            self::Loaned => 'Dipinjam',
            self::Lost => 'Hilang',
            self::Archived => 'Arsip',
        };
    }
}
