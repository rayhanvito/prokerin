<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\User;
use App\Notifications\InventoryLoanOverdueNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;

final class CheckOverdueInventoryLoansJob implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        $loans = DB::table('inventory_loans')
            ->join('inventory_items', 'inventory_items.id', '=', 'inventory_loans.item_id')
            ->where('inventory_loans.status', 'approved')
            ->whereNull('inventory_loans.returned_at')
            ->whereNull('inventory_loans.overdue_notified_at')
            ->where('inventory_loans.expected_return_at', '<', now())
            ->limit(100)
            ->get([
                'inventory_loans.id',
                'inventory_loans.borrower_user_id',
                'inventory_loans.expected_return_at',
                'inventory_items.name as item_name',
            ]);

        foreach ($loans as $loan) {
            $borrower = User::query()->find((int) $loan->borrower_user_id);

            if ($borrower !== null) {
                $borrower->notify(new InventoryLoanOverdueNotification(
                    itemName: (string) $loan->item_name,
                    expectedReturnAt: (string) $loan->expected_return_at,
                ));
            }

            DB::table('inventory_loans')
                ->where('id', $loan->id)
                ->update(['overdue_notified_at' => now(), 'updated_at' => now()]);
        }
    }
}
