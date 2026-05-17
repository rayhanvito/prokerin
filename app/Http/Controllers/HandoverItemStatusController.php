<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Handover\UpdateHandoverItemStatusAction;
use App\Http\Requests\UpdateHandoverItemStatusRequest;
use App\Support\OrganizationModeGate;
use Illuminate\Http\RedirectResponse;

final class HandoverItemStatusController extends Controller
{
    public function update(
        UpdateHandoverItemStatusRequest $request,
        int $item,
        UpdateHandoverItemStatusAction $updateHandoverItemStatus,
    ): RedirectResponse {
        abort_unless(OrganizationModeGate::forRequest($request)->canUseHandover(), 403);

        $updateHandoverItemStatus->execute(
            actorUserId: (int) $request->user()->id,
            handoverItemId: $item,
            status: (string) $request->validated('status'),
        );

        return back()->with('success', 'Status item handover berhasil diperbarui.');
    }
}
