<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Document\StoreDocumentAction;
use App\Domain\Document\DocumentVisibility;
use App\Http\Requests\StoreDocumentRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\UploadedFile;

final class DocumentController extends Controller
{
    public function store(StoreDocumentRequest $request, StoreDocumentAction $storeDocument): RedirectResponse
    {
        $file = $request->file('file');

        if (! $file instanceof UploadedFile) {
            return back()->withErrors(['file' => 'File dokumen wajib diunggah.']);
        }

        $storeDocument->execute(
            actorUserId: (int) $request->user()->id,
            file: $file,
            folder: (string) $request->validated('folder'),
            visibility: DocumentVisibility::from((string) $request->validated('visibility')),
            projectId: $request->validated('project_id') === null ? null : (int) $request->validated('project_id'),
            preferredOrganizationId: $request->session()->get('active_organization_id') === null
                ? null
                : (int) $request->session()->get('active_organization_id'),
        );

        return back()->with('success', 'Dokumen berhasil diunggah.');
    }
}
