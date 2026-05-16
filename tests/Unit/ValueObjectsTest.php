<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Support\ValueObjects\Money;
use App\Support\ValueObjects\Progress;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class ValueObjectsTest extends TestCase
{
    public function test_money_formats_rupiah(): void
    {
        $this->assertSame('Rp6.500.000', Money::rupiah(6500000)->formatted());
    }

    public function test_money_rejects_negative_amounts(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Money::rupiah(-1);
    }

    public function test_money_requires_matching_currency_for_arithmetic(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Money::rupiah(1000)->add(new Money(1000, 'USD'));
    }

    public function test_progress_can_be_created_from_completed_items(): void
    {
        $progress = Progress::fromCompletedItems(3, 4);

        $this->assertSame(75, $progress->percentage);
        $this->assertFalse($progress->isComplete());
    }

    public function test_progress_rejects_out_of_range_percentage(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new Progress(101);
    }
}
