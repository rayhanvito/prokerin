<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Handover\AssignHandoverTransitionAction;
use App\Http\Requests\UpdateHandoverTransitionRequest;
use App\Support\OrganizationModeGate;
use Illuminate\Http\RedirectResponse;

final class HandoverPackageTransitionController extends Controller
{
    public function update(
        UpdateHandoverTransitionRequest $request,
        int $package,
        AssignHandoverTransitionAction $assignHandoverTransition,
    ): RedirectResponse {
        abort_unless(OrganizationModeGate::forRequest($request)->canUseHandover(), 403);

        $assignHandoverTransition->execute(
            actorUserId: (int) $request->user()->id,
            handoverPackageId: $package,
            toPeriodId: $request->integer('to_period_id') === 0 ? null : $request->integer('to_period_id'),
            incomingOwnerId: $request->integer('incoming_owner_id') === 0 ? null : $request->integer('incoming_owner_id'),
        );

        return back()->with('success', 'Penerima handover berhasil diperbarui.');
    }
}
