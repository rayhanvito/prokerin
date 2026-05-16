<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Document\CreateDocumentDownloadUrlAction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class DocumentDownloadController extends Controller
{
    public function show(
        Request $request,
        int $document,
        CreateDocumentDownloadUrlAction $createDocumentDownloadUrl,
    ): RedirectResponse {
        return redirect()->away($createDocumentDownloadUrl->execute(
            actorUserId: (int) $request->user()->id,
            documentId: $document,
        ));
    }
}
