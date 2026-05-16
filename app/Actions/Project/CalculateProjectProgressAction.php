<?php

declare(strict_types=1);

namespace App\Actions\Project;

use App\Support\ValueObjects\Progress;

final class CalculateProjectProgressAction
{
    /**
     * @param  array<int, bool>  $completionFlags
     */
    public function execute(array $completionFlags): Progress
    {
        $completed = count(array_filter($completionFlags));

        return Progress::fromCompletedItems($completed, count($completionFlags));
    }
}
