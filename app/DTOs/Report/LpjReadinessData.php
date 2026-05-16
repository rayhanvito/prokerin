<?php

declare(strict_types=1);

namespace App\DTOs\Report;

use App\Support\ValueObjects\Progress;

final readonly class LpjReadinessData
{
    /**
     * @param  array<int, string>  $missingRequiredItems
     */
    public function __construct(
        public int $requiredItemCount,
        public int $completedRequiredItemCount,
        public Progress $completionProgress,
        public bool $isReadyForReview,
        public array $missingRequiredItems,
    ) {}

    /**
     * @return array{
     *     requiredItemCount: int,
     *     completedRequiredItemCount: int,
     *     completionProgress: int,
     *     isReadyForReview: bool,
     *     missingRequiredItems: array<int, string>
     * }
     */
    public function toArray(): array
    {
        return [
            'requiredItemCount' => $this->requiredItemCount,
            'completedRequiredItemCount' => $this->completedRequiredItemCount,
            'completionProgress' => $this->completionProgress->percentage,
            'isReadyForReview' => $this->isReadyForReview,
            'missingRequiredItems' => $this->missingRequiredItems,
        ];
    }
}
