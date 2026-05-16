<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\DocumentExport\CreateDocumentExportDownloadUrlAction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class DocumentExportDownloadController extends Controller
{
    public function show(
        Request $request,
        int $documentExport,
        CreateDocumentExportDownloadUrlAction $createDocumentExportDownloadUrl,
    ): RedirectResponse {
        return redirect()->away($createDocumentExportDownloadUrl->execute(
            actorUserId: (int) $request->user()->id,
            documentExportId: $documentExport,
        ));
    }
}
