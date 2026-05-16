<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Ai\DraftProposalWithAiAction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class ProposalAiSuggestionController extends Controller
{
    public function store(
        Request $request,
        int $proposalDraft,
        DraftProposalWithAiAction $draftProposalWithAi,
    ): RedirectResponse {
        $suggestion = $draftProposalWithAi->execute(
            actorUserId: (int) $request->user()->id,
            proposalDraftId: $proposalDraft,
        );

        return back()
            ->with('success', 'Saran AI proposal berhasil dibuat.')
            ->with('aiSuggestion', [
                'type' => 'proposal_draft',
                'proposalDraftId' => $proposalDraft,
                'sections' => $suggestion['sections'],
                'promptHash' => $suggestion['promptHash'],
            ]);
    }
}
