<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Proposal\DecideProposalApprovalAction;
use App\Domain\Proposal\ProposalApprovalDecision;
use App\Http\Requests\DecideProposalApprovalRequest;
use App\Models\User;
use App\Notifications\ProposalApprovalDecidedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

final class ProposalApprovalDecisionController extends Controller
{
    public function update(
        DecideProposalApprovalRequest $request,
        int $proposalDraft,
        DecideProposalApprovalAction $decideProposalApproval,
    ): RedirectResponse {
        $decision = ProposalApprovalDecision::from((string) $request->validated('decision'));
        $actor = $request->user();

        $decideProposalApproval->execute(
            actorUserId: (int) $actor->id,
            proposalDraftId: $proposalDraft,
            decision: $decision,
        );

        $this->notifySubmitter($proposalDraft, $decision, (string) $actor->name);

        $message = $decision === ProposalApprovalDecision::Approve
            ? 'Proposal disetujui dan proker masuk tahap RAB approval.'
            : 'Proposal dikembalikan untuk revisi.';

        return back()->with('success', $message);
    }

    private function notifySubmitter(int $proposalDraftId, ProposalApprovalDecision $decision, string $approverName): void
    {
        $context = DB::table('proposal_drafts')
            ->join('projects', 'projects.id', '=', 'proposal_drafts.project_id')
            ->where('proposal_drafts.id', $proposalDraftId)
            ->first([
                'projects.name as project_name',
                'projects.project_lead_id',
            ]);

        if ($context === null || $context->project_lead_id === null) {
            return;
        }

        $submitter = User::query()->find((int) $context->project_lead_id);

        if ($submitter === null) {
            return;
        }

        $submitter->notify(new ProposalApprovalDecidedNotification(
            projectName: (string) $context->project_name,
            decision: $decision === ProposalApprovalDecision::Approve ? 'approved' : 'revision_requested',
            approverName: $approverName,
            resourceUrl: route('reports.proposal-editor', absolute: true),
        ));
    }
}
