<?php

declare(strict_types=1);

namespace App\DTOs\Document;

use App\Domain\Document\DocumentVisibility;

final readonly class DocumentDownloadRequestData
{
    public function __construct(
        public string $storagePath,
        public string $originalName,
        public DocumentVisibility $visibility,
    ) {}
}
