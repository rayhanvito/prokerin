<?php

declare(strict_types=1);

namespace App\Actions\Document;

use App\DTOs\Document\DocumentUploadCandidateData;
use App\DTOs\Document\DocumentUploadValidationData;

final class ValidateDocumentUploadAction
{
    private const MAX_FILE_SIZE_KB = 10240;

    /**
     * @var array<int, string>
     */
    private const ALLOWED_MIME_TYPES = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'image/jpeg',
        'image/png',
        'application/zip',
        'application/x-zip-compressed',
    ];

    public function execute(DocumentUploadCandidateData $candidate): DocumentUploadValidationData
    {
        $errors = [];

        if (! in_array($candidate->mimeType, self::ALLOWED_MIME_TYPES, true)) {
            $errors[] = 'Document MIME type is not allowed.';
        }

        if ($candidate->sizeInKilobytes <= 0) {
            $errors[] = 'Document file size must be greater than zero.';
        }

        if ($candidate->sizeInKilobytes > self::MAX_FILE_SIZE_KB) {
            $errors[] = 'Document file size exceeds 10 MB.';
        }

        return new DocumentUploadValidationData(
            isValid: $errors === [],
            errors: $errors,
            requiresSignedUrl: $candidate->visibility->requiresSignedUrl(),
        );
    }
}
