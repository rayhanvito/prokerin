<?php

declare(strict_types=1);

namespace App\Actions\Document;

use App\DTOs\Document\DocumentDownloadPlanData;
use App\DTOs\Document\DocumentDownloadRequestData;
use DomainException;

final class PlanDocumentDownloadAction
{
    public function execute(DocumentDownloadRequestData $request): DocumentDownloadPlanData
    {
        if ($request->storagePath === '' || str_contains($request->storagePath, '..')) {
            throw new DomainException('Document storage path is invalid.');
        }

        return new DocumentDownloadPlanData(
            disk: 's3',
            path: $request->storagePath,
            downloadName: $request->originalName,
            expiresInMinutes: $request->visibility->requiresSignedUrl() ? 10 : 0,
            requiresSignedUrl: $request->visibility->requiresSignedUrl(),
        );
    }
}
