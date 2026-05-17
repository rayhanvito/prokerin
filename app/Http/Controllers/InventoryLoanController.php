<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Inventory\ApproveInventoryLoanAction;
use App\Actions\Inventory\RequestInventoryLoanAction;
use App\Actions\Inventory\ReturnInventoryLoanAction;
use App\Http\Requests\RequestLoanRequest;
use App\Http\Requests\ReturnLoanRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class InventoryLoanController extends Controller
{
    public function store(RequestLoanRequest $request, int $item, RequestInventoryLoanAction $requestLoan): RedirectResponse
    {
        $requestLoan->execute(
            actorUserId: (int) $request->user()->id,
            itemId: $item,
            projectId: $request->validated('project_id') === null ? null : (int) $request->validated('project_id'),
            expectedReturnAt: (string) $request->validated('expected_return_at'),
            notes: $request->validated('notes') === null ? null : (string) $request->validated('notes'),
        );

        return back()->with('success', 'Permintaan peminjaman inventaris dikirim.');
    }

    public function approve(Request $request, int $loan, ApproveInventoryLoanAction $approveLoan): RedirectResponse
    {
        $approveLoan->execute((int) $request->user()->id, $loan);

        return back()->with('success', 'Peminjaman inventaris disetujui.');
    }

    public function return(ReturnLoanRequest $request, int $loan, ReturnInventoryLoanAction $returnLoan): RedirectResponse
    {
        $returnLoan->execute(
            actorUserId: (int) $request->user()->id,
            loanId: $loan,
            returnCondition: (string) $request->validated('return_condition'),
            notes: $request->validated('notes') === null ? null : (string) $request->validated('notes'),
        );

        return back()->with('success', 'Inventaris dicatat sudah kembali.');
    }
}
