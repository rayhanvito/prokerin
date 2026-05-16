<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Finance\StoreBudgetReceiptRealizationAction;
use App\Http\Requests\StoreBudgetReceiptRealizationRequest;
use Illuminate\Http\RedirectResponse;

final class BudgetReceiptRealizationController extends Controller
{
    public function store(
        StoreBudgetReceiptRealizationRequest $request,
        int $budgetLine,
        StoreBudgetReceiptRealizationAction $storeBudgetReceiptRealization,
    ): RedirectResponse {
        $storeBudgetReceiptRealization->execute(
            actorUserId: (int) $request->user()->id,
            budgetLineId: $budgetLine,
            transactionName: (string) $request->validated('name'),
            amount: (int) $request->validated('amount'),
            receipt: $request->file('receipt'),
        );

        return back()->with('success', 'Realisasi anggaran berhasil dicatat dan receipt masuk antrean review.');
    }
}
