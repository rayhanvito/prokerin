<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\EventRegistration\QueueEventRegistrationPdfExportAction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class EventRegistrationPdfExportController extends Controller
{
    public function store(
        Request $request,
        int $project,
        QueueEventRegistrationPdfExportAction $queuePdfExport,
    ): RedirectResponse {
        $queuePdfExport->execute(
            actorUserId: (int) $request->user()->id,
            projectId: $project,
        );

        return back()->with('success', 'Export PDF peserta event masuk antrean.');
    }
}
