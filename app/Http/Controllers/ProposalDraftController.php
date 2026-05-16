<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Proposal\UpdateProposalDraftSectionsAction;
use App\Http\Requests\UpdateProposalDraftSectionsRequest;
use Illuminate\Http\RedirectResponse;

final class ProposalDraftController extends Controller
{
    public function update(
        UpdateProposalDraftSectionsRequest $request,
        int $proposalDraft,
        UpdateProposalDraftSectionsAction $updateProposalDraft,
    ): RedirectResponse {
        $updateProposalDraft->execute(
            actorUserId: (int) $request->user()->id,
            proposalDraftId: $proposalDraft,
            sections: $request->validated('sections'),
        );

        return back()->with('success', 'Draft proposal berhasil disimpan.');
    }
}
