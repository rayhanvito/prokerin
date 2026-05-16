<?php

declare(strict_types=1);

namespace App\Actions\Report;

use App\DTOs\Report\LpjChecklistItemData;
use App\DTOs\Report\LpjReadinessData;
use App\Support\ValueObjects\Progress;

final class CalculateLpjReadinessAction
{
    /**
     * @param  array<int, LpjChecklistItemData>  $items
     */
    public function execute(array $items): LpjReadinessData
    {
        $requiredItems = array_values(array_filter(
            $items,
            static fn (LpjChecklistItemData $item): bool => $item->isRequired,
        ));
        $completedRequiredItems = array_values(array_filter(
            $requiredItems,
            static fn (LpjChecklistItemData $item): bool => $item->isComplete,
        ));
        $missingRequiredItems = array_map(
            static fn (LpjChecklistItemData $item): string => $item->title,
            array_values(array_filter(
                $requiredItems,
                static fn (LpjChecklistItemData $item): bool => ! $item->isComplete,
            )),
        );

        return new LpjReadinessData(
            requiredItemCount: count($requiredItems),
            completedRequiredItemCount: count($completedRequiredItems),
            completionProgress: Progress::fromCompletedItems(count($completedRequiredItems), count($requiredItems)),
            isReadyForReview: $requiredItems !== [] && $missingRequiredItems === [],
            missingRequiredItems: $missingRequiredItems,
        );
    }
}
