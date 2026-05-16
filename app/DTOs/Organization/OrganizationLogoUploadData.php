<?php

declare(strict_types=1);

namespace App\DTOs\Organization;

final readonly class OrganizationLogoUploadData
{
    public function __construct(
        public string $organizationId,
        public string $originalName,
        public string $mimeType,
        public int $sizeInKilobytes,
    ) {}
}
