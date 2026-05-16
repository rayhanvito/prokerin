<?php

declare(strict_types=1);

namespace App\DTOs\Document;

use App\Domain\Document\DocumentVisibility;

final readonly class DocumentUploadCandidateData
{
    public function __construct(
        public string $originalName,
        public string $mimeType,
        public int $sizeInKilobytes,
        public DocumentVisibility $visibility,
    ) {}
}
