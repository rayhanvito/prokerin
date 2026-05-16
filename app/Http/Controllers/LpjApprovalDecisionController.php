<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Report\DecideLpjApprovalAction;
use App\Domain\Report\LpjApprovalDecision;
use App\Http\Requests\DecideLpjApprovalRequest;
use Illuminate\Http\RedirectResponse;

final class LpjApprovalDecisionController extends Controller
{
    public function update(
        DecideLpjApprovalRequest $request,
        int $project,
        DecideLpjApprovalAction $decideLpjApproval,
    ): RedirectResponse {
        $decision = LpjApprovalDecision::from((string) $request->validated('decision'));

        $decideLpjApproval->execute(
            actorUserId: (int) $request->user()->id,
            projectId: $project,
            decision: $decision,
        );

        $message = $decision === LpjApprovalDecision::Approve
            ? 'LPJ disetujui dan proker selesai.'
            : 'LPJ dikembalikan untuk revisi.';

        return back()->with('success', $message);
    }
}
