<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Finance\CreateBudgetLineAction;
use App\Actions\Finance\DeleteBudgetLineAction;
use App\Actions\Finance\UpdateBudgetLineAction;
use App\Http\Requests\StoreBudgetLineRequest;
use App\Http\Requests\UpdateBudgetLineRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class BudgetLineController extends Controller
{
    public function store(StoreBudgetLineRequest $request, CreateBudgetLineAction $createBudgetLine): RedirectResponse
    {
        $createBudgetLine->execute(
            actorUserId: (int) $request->user()->id,
            input: [
                'project_id' => (int) $request->validated()['project_id'],
                'name' => (string) $request->validated()['name'],
                'category' => (string) $request->validated()['category'],
                'planned_amount' => (int) $request->validated()['planned_amount'],
            ],
        );

        return back()->with('success', 'Budget line berhasil ditambahkan.');
    }

    public function update(
        UpdateBudgetLineRequest $request,
        int $budgetLine,
        UpdateBudgetLineAction $updateBudgetLine,
    ): RedirectResponse {
        $updateBudgetLine->execute(
            actorUserId: (int) $request->user()->id,
            budgetLineId: $budgetLine,
            input: $request->validated(),
        );

        return back()->with('success', 'Budget line berhasil diperbarui.');
    }

    public function destroy(
        Request $request,
        int $budgetLine,
        DeleteBudgetLineAction $deleteBudgetLine,
    ): RedirectResponse {
        $deleteBudgetLine->execute(
            actorUserId: (int) $request->user()->id,
            budgetLineId: $budgetLine,
        );

        return back()->with('success', 'Budget line berhasil dihapus.');
    }
}
