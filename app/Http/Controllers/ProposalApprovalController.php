<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Proposal\SubmitProposalDraftForApprovalAction;
use App\Http\Requests\SubmitProposalDraftRequest;
use Illuminate\Http\RedirectResponse;

final class ProposalApprovalController extends Controller
{
    public function store(
        SubmitProposalDraftRequest $request,
        int $proposalDraft,
        SubmitProposalDraftForApprovalAction $submitProposalDraft,
    ): RedirectResponse {
        $submitProposalDraft->execute(
            actorUserId: (int) $request->user()->id,
            proposalDraftId: $proposalDraft,
        );

        return back()->with('success', 'Proposal berhasil dikirim ke approval dan export PDF masuk antrean.');
    }
}
