<?php

declare(strict_types=1);

namespace App\Domain\Inventory;

enum InventoryCondition: string
{
    case Excellent = 'excellent';
    case Good = 'good';
    case NeedsRepair = 'needs_repair';
    case Broken = 'broken';

    public function label(): string
    {
        return match ($this) {
            self::Excellent => 'Excellent',
            self::Good => 'Good',
            self::NeedsRepair => 'Perlu perbaikan',
            self::Broken => 'Rusak',
        };
    }
}
