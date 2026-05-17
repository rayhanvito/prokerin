<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Report\DecideLpjApprovalAction;
use App\Domain\Report\LpjApprovalDecision;
use App\Http\Requests\DecideLpjApprovalRequest;
use App\Models\User;
use App\Notifications\LpjApprovalDecidedNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

final class LpjApprovalDecisionController extends Controller
{
    public function update(
        DecideLpjApprovalRequest $request,
        int $project,
        DecideLpjApprovalAction $decideLpjApproval,
    ): RedirectResponse {
        $decision = LpjApprovalDecision::from((string) $request->validated('decision'));
        $actor = $request->user();

        $decideLpjApproval->execute(
            actorUserId: (int) $actor->id,
            projectId: $project,
            decision: $decision,
        );

        $this->notifySubmitter($project, $decision, (string) $actor->name);

        $message = $decision === LpjApprovalDecision::Approve
            ? 'LPJ disetujui dan proker selesai.'
            : 'LPJ dikembalikan untuk revisi.';

        return back()->with('success', $message);
    }

    private function notifySubmitter(int $projectId, LpjApprovalDecision $decision, string $approverName): void
    {
        $project = DB::table('projects')->where('id', $projectId)->first(['name', 'project_lead_id']);

        if ($project === null || $project->project_lead_id === null) {
            return;
        }

        $submitter = User::query()->find((int) $project->project_lead_id);

        if ($submitter === null) {
            return;
        }

        $submitter->notify(new LpjApprovalDecidedNotification(
            projectName: (string) $project->name,
            decision: $decision === LpjApprovalDecision::Approve ? 'approved' : 'revision_requested',
            approverName: $approverName,
            resourceUrl: route('reports.lpj-checklist', absolute: true),
        ));
    }
}
