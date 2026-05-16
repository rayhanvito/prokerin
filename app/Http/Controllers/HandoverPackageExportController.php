<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Handover\QueueHandoverPackageExportAction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class HandoverPackageExportController extends Controller
{
    public function store(
        Request $request,
        int $package,
        QueueHandoverPackageExportAction $queueHandoverPackageExport,
    ): RedirectResponse {
        $queueHandoverPackageExport->execute(
            actorUserId: (int) $request->user()->id,
            handoverPackageId: $package,
        );

        return back()->with('success', 'Export handover PDF masuk antrean.');
    }
}
