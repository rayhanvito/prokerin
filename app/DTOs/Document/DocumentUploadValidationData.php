<?php

declare(strict_types=1);

namespace App\DTOs\Document;

final readonly class DocumentUploadValidationData
{
    /**
     * @param  array<int, string>  $errors
     */
    public function __construct(
        public bool $isValid,
        public array $errors,
        public bool $requiresSignedUrl,
    ) {}
}
