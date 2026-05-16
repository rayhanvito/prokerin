<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Handover\UpdateHandoverItemStatusAction;
use App\Http\Requests\UpdateHandoverItemStatusRequest;
use Illuminate\Http\RedirectResponse;

final class HandoverItemStatusController extends Controller
{
    public function update(
        UpdateHandoverItemStatusRequest $request,
        int $item,
        UpdateHandoverItemStatusAction $updateHandoverItemStatus,
    ): RedirectResponse {
        $updateHandoverItemStatus->execute(
            actorUserId: (int) $request->user()->id,
            handoverItemId: $item,
            status: (string) $request->validated('status'),
        );

        return back()->with('success', 'Status item handover berhasil diperbarui.');
    }
}
