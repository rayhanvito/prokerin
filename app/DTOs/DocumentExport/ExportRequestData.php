<?php

declare(strict_types=1);

namespace App\DTOs\DocumentExport;

use App\Domain\DocumentExport\ExportDocumentType;
use App\Domain\DocumentExport\ExportFormat;

final readonly class ExportRequestData
{
    public function __construct(
        public string $documentId,
        public string $documentTitle,
        public ExportDocumentType $documentType,
        public ExportFormat $format,
        public string $requestedBy,
    ) {}
}
