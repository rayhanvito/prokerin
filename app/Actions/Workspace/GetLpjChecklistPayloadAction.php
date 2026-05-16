<?php

declare(strict_types=1);

namespace App\Actions\Workspace;

use App\Actions\Report\CalculateLpjReadinessAction;
use App\DTOs\Report\LpjChecklistItemData;
use Illuminate\Support\Facades\DB;

final readonly class GetLpjChecklistPayloadAction
{
    public function __construct(private CalculateLpjReadinessAction $readiness) {}

    /**
     * @return array{checklistItems: array<int, array{title: string, isComplete: bool, isRequired: bool}>, readiness: array<string, mixed>}
     */
    public function execute(): array
    {
        $items = DB::table('lpj_checklist_items')
            ->orderBy('id')
            ->get()
            ->map(static fn (object $item): LpjChecklistItemData => new LpjChecklistItemData(
                title: (string) $item->title,
                isComplete: (bool) $item->is_complete,
                isRequired: (bool) $item->is_required,
            ))
            ->all();

        return [
            'checklistItems' => array_map(
                static fn (LpjChecklistItemData $item): array => [
                    'title' => $item->title,
                    'isComplete' => $item->isComplete,
                    'isRequired' => $item->isRequired,
                ],
                $items,
            ),
            'readiness' => $this->readiness->execute($items)->toArray(),
        ];
    }
}
