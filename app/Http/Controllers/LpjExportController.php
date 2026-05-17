<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Report\QueueLpjExportAction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class LpjExportController extends Controller
{
    public function store(Request $request, int $project, QueueLpjExportAction $queueLpjExport): RedirectResponse
    {
        $queueLpjExport->execute(
            actorUserId: (int) $request->user()->id,
            projectId: $project,
        );

        return back()->with('success', 'Export LPJ PDF berhasil di-queue.');
    }
}
