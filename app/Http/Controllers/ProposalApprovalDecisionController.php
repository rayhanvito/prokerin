<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Proposal\DecideProposalApprovalAction;
use App\Domain\Proposal\ProposalApprovalDecision;
use App\Http\Requests\DecideProposalApprovalRequest;
use Illuminate\Http\RedirectResponse;

final class ProposalApprovalDecisionController extends Controller
{
    public function update(
        DecideProposalApprovalRequest $request,
        int $proposalDraft,
        DecideProposalApprovalAction $decideProposalApproval,
    ): RedirectResponse {
        $decision = ProposalApprovalDecision::from((string) $request->validated('decision'));

        $decideProposalApproval->execute(
            actorUserId: (int) $request->user()->id,
            proposalDraftId: $proposalDraft,
            decision: $decision,
        );

        $message = $decision === ProposalApprovalDecision::Approve
            ? 'Proposal disetujui dan proker masuk tahap RAB approval.'
            : 'Proposal dikembalikan untuk revisi.';

        return back()->with('success', $message);
    }
}
