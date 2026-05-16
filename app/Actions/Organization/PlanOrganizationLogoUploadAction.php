<?php

declare(strict_types=1);

namespace App\Actions\Organization;

use App\DTOs\Organization\OrganizationLogoPlanData;
use App\DTOs\Organization\OrganizationLogoUploadData;

final class PlanOrganizationLogoUploadAction
{
    private const MAX_LOGO_SIZE_KB = 2048;

    /**
     * @var array<int, string>
     */
    private const ALLOWED_MIME_TYPES = [
        'image/jpeg',
        'image/png',
        'image/webp',
    ];

    public function execute(OrganizationLogoUploadData $upload): OrganizationLogoPlanData
    {
        $errors = [];

        if (! in_array($upload->mimeType, self::ALLOWED_MIME_TYPES, true)) {
            $errors[] = 'Organization logo must be a JPG, PNG, or WEBP image.';
        }

        if ($upload->sizeInKilobytes <= 0) {
            $errors[] = 'Organization logo file size must be greater than zero.';
        }

        if ($upload->sizeInKilobytes > self::MAX_LOGO_SIZE_KB) {
            $errors[] = 'Organization logo file size exceeds 2 MB.';
        }

        return new OrganizationLogoPlanData(
            isValid: $errors === [],
            disk: 's3',
            storagePath: $errors === [] ? $this->storagePath($upload) : null,
            errors: $errors,
        );
    }

    private function storagePath(OrganizationLogoUploadData $upload): string
    {
        $extension = match ($upload->mimeType) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => 'bin',
        };

        return sprintf('organizations/%s/logo.%s', $upload->organizationId, $extension);
    }
}
