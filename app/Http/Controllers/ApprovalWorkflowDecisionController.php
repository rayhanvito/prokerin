<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Approval\ProcessApprovalStepAction;
use App\Http\Requests\ProcessApprovalStepRequest;
use Illuminate\Http\RedirectResponse;

final class ApprovalWorkflowDecisionController extends Controller
{
    public function update(
        ProcessApprovalStepRequest $request,
        int $instance,
        ProcessApprovalStepAction $processApprovalStep,
    ): RedirectResponse {
        $processApprovalStep->execute(
            actorUserId: (int) $request->user()->id,
            instanceId: $instance,
            decision: (string) $request->validated('decision'),
            note: $request->validated('note'),
        );

        return back()->with('success', 'Workflow approval berhasil diproses.');
    }
}
