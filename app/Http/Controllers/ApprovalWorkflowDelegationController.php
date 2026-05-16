<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Approval\DelegateApprovalStepAction;
use App\Http\Requests\DelegateApprovalStepRequest;
use Illuminate\Http\RedirectResponse;

final class ApprovalWorkflowDelegationController extends Controller
{
    public function update(
        DelegateApprovalStepRequest $request,
        int $instance,
        DelegateApprovalStepAction $delegateApprovalStep,
    ): RedirectResponse {
        $delegateApprovalStep->execute(
            actorUserId: (int) $request->user()->id,
            instanceId: $instance,
            delegateUserId: (int) $request->validated('delegate_user_id'),
            note: $request->validated('note'),
        );

        return back()->with('success', 'Workflow approval berhasil didelegasikan.');
    }
}
