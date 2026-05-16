<?php

declare(strict_types=1);

namespace App\DTOs\Document;

final readonly class DocumentDownloadPlanData
{
    public function __construct(
        public string $disk,
        public string $path,
        public string $downloadName,
        public int $expiresInMinutes,
        public bool $requiresSignedUrl,
    ) {}
}
