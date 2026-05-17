<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\DocumentExport\RetryDocumentExportAction;
use App\Models\DocumentExport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

final class DocumentExportRetryController extends Controller
{
    public function store(
        Request $request,
        DocumentExport $documentExport,
        RetryDocumentExportAction $retryDocumentExport,
    ): RedirectResponse {
        abort_unless($request->user()?->hasRole('super_admin') === true, 403);

        $retryDocumentExport->execute($documentExport, (int) $request->user()->id);

        return back()->with('success', 'Export berhasil masuk antrean ulang.');
    }
}
