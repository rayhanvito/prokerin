<?php

declare(strict_types=1);

namespace App\Actions\Workspace;

use App\Domain\Inventory\InventoryCondition;
use App\Domain\Inventory\InventoryStatus;
use App\Domain\Inventory\LoanReturnCondition;
use App\Support\Roles;
use Illuminate\Support\Facades\DB;

final class GetInventoryPayloadAction
{
    public function __construct(private readonly GetActiveOrganizationContextAction $activeOrganizationContext) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(int $actorUserId, ?int $preferredOrganizationId = null, ?int $itemId = null, ?string $qrToken = null): array
    {
        $context = $this->activeOrganizationContext->execute($actorUserId, $preferredOrganizationId);
        $canManage = in_array($context->role, Roles::SECRETARY_AND_UP, true);

        $itemsQuery = DB::table('inventory_items')
            ->where('organization_id', $context->organizationId)
            ->whereNull('deleted_at');

        $items = (clone $itemsQuery)
            ->orderBy('category')
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'category',
                'location',
                'condition',
                'status',
                'qr_token',
                'updated_at',
            ]);

        $selectedItem = null;
        if ($itemId !== null || $qrToken !== null) {
            $selectedItem = (clone $itemsQuery)
                ->when($itemId !== null, static fn ($query) => $query->where('id', $itemId))
                ->when($qrToken !== null, static fn ($query) => $query->where('qr_token', $qrToken))
                ->first();

            abort_if($selectedItem === null, 404);
        }

        return [
            'metrics' => [
                'total' => (clone $itemsQuery)->count(),
                'available' => (clone $itemsQuery)->where('status', InventoryStatus::Available->value)->count(),
                'loaned' => (clone $itemsQuery)->where('status', InventoryStatus::Loaned->value)->count(),
                'needsAttention' => (clone $itemsQuery)
                    ->where(static function ($query): void {
                        $query->whereIn('condition', [InventoryCondition::NeedsRepair->value, InventoryCondition::Broken->value])
                            ->orWhere('status', InventoryStatus::Lost->value);
                    })
                    ->count(),
            ],
            'items' => $items->map(fn (object $item): array => $this->itemRow($item))->all(),
            'item' => $selectedItem === null ? null : $this->itemDetail($selectedItem),
            'loans' => $selectedItem === null ? [] : $this->loanRows((int) $selectedItem->id),
            'projects' => DB::table('projects')
                ->where('organization_id', $context->organizationId)
                ->orderByDesc('starts_at')
                ->limit(50)
                ->get(['id', 'name'])
                ->map(static fn (object $project): array => [
                    'id' => (int) $project->id,
                    'name' => (string) $project->name,
                ])
                ->all(),
            'canManage' => $canManage,
            'options' => [
                'conditions' => array_map(static fn (InventoryCondition $condition): array => [
                    'value' => $condition->value,
                    'label' => $condition->label(),
                ], InventoryCondition::cases()),
                'statuses' => array_map(static fn (InventoryStatus $status): array => [
                    'value' => $status->value,
                    'label' => $status->label(),
                ], InventoryStatus::cases()),
                'returnConditions' => array_map(static fn (LoanReturnCondition $condition): array => [
                    'value' => $condition->value,
                    'label' => $condition->label(),
                ], LoanReturnCondition::cases()),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function itemRow(object $item): array
    {
        return [
            'id' => (int) $item->id,
            'name' => (string) $item->name,
            'category' => (string) $item->category,
            'location' => $item->location === null ? null : (string) $item->location,
            'condition' => (string) $item->condition,
            'conditionLabel' => InventoryCondition::from((string) $item->condition)->label(),
            'status' => (string) $item->status,
            'statusLabel' => InventoryStatus::from((string) $item->status)->label(),
            'qrToken' => (string) $item->qr_token,
            'updatedAt' => (string) $item->updated_at,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function itemDetail(object $item): array
    {
        return [
            ...$this->itemRow($item),
            'description' => $item->description === null ? null : (string) $item->description,
            'photoPath' => $item->photo_path === null ? null : (string) $item->photo_path,
            'purchasedAt' => $item->purchased_at === null ? null : (string) $item->purchased_at,
            'purchaseAmount' => $item->purchase_amount === null ? null : (int) $item->purchase_amount,
            'qrUrl' => route('inventory.qr.show', ['token' => $item->qr_token], absolute: false),
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function loanRows(int $itemId): array
    {
        return DB::table('inventory_loans')
            ->join('users as borrowers', 'borrowers.id', '=', 'inventory_loans.borrower_user_id')
            ->leftJoin('users as approvers', 'approvers.id', '=', 'inventory_loans.approved_by_user_id')
            ->leftJoin('projects', 'projects.id', '=', 'inventory_loans.project_id')
            ->where('inventory_loans.item_id', $itemId)
            ->orderByDesc('inventory_loans.created_at')
            ->get([
                'inventory_loans.id',
                'inventory_loans.status',
                'inventory_loans.loaned_at',
                'inventory_loans.expected_return_at',
                'inventory_loans.returned_at',
                'inventory_loans.return_condition',
                'inventory_loans.notes',
                'borrowers.name as borrower_name',
                'approvers.name as approver_name',
                'projects.name as project_name',
            ])
            ->map(static fn (object $loan): array => [
                'id' => (int) $loan->id,
                'status' => (string) $loan->status,
                'borrowerName' => (string) $loan->borrower_name,
                'projectName' => $loan->project_name === null ? null : (string) $loan->project_name,
                'approvedBy' => $loan->approver_name === null ? null : (string) $loan->approver_name,
                'loanedAt' => $loan->loaned_at === null ? null : (string) $loan->loaned_at,
                'expectedReturnAt' => (string) $loan->expected_return_at,
                'returnedAt' => $loan->returned_at === null ? null : (string) $loan->returned_at,
                'returnCondition' => $loan->return_condition === null ? null : (string) $loan->return_condition,
                'notes' => $loan->notes === null ? null : (string) $loan->notes,
            ])
            ->all();
    }
}
