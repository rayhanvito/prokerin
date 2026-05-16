<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Actions\Project\CalculateProjectProgressAction;
use PHPUnit\Framework\TestCase;

final class CalculateProjectProgressActionTest extends TestCase
{
    public function test_it_calculates_progress_from_completion_flags(): void
    {
        $progress = (new CalculateProjectProgressAction)->execute([
            true,
            true,
            false,
            false,
        ]);

        $this->assertSame(50, $progress->percentage);
    }

    public function test_empty_completion_flags_return_zero_progress(): void
    {
        $progress = (new CalculateProjectProgressAction)->execute([]);

        $this->assertSame(0, $progress->percentage);
    }
}
