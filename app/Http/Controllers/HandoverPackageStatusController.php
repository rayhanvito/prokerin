<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Handover\UpdateHandoverPackageStatusAction;
use App\Http\Requests\UpdateHandoverPackageStatusRequest;
use App\Support\OrganizationModeGate;
use Illuminate\Http\RedirectResponse;

final class HandoverPackageStatusController extends Controller
{
    public function update(
        UpdateHandoverPackageStatusRequest $request,
        int $package,
        UpdateHandoverPackageStatusAction $updateHandoverPackageStatus,
    ): RedirectResponse {
        abort_unless(OrganizationModeGate::forRequest($request)->canUseHandover(), 403);

        $updateHandoverPackageStatus->execute(
            actorUserId: (int) $request->user()->id,
            handoverPackageId: $package,
            status: (string) $request->validated('status'),
        );

        return back()->with('success', 'Status paket handover berhasil diperbarui.');
    }
}
