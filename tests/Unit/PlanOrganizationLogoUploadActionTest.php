<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Actions\Organization\PlanOrganizationLogoUploadAction;
use App\DTOs\Organization\OrganizationLogoUploadData;
use PHPUnit\Framework\TestCase;

final class PlanOrganizationLogoUploadActionTest extends TestCase
{
    public function test_it_plans_valid_organization_logo_upload(): void
    {
        $plan = (new PlanOrganizationLogoUploadAction)->execute(
            new OrganizationLogoUploadData(
                organizationId: 'org-bem-ft',
                originalName: 'logo.png',
                mimeType: 'image/png',
                sizeInKilobytes: 512,
            ),
        );

        $this->assertTrue($plan->isValid);
        $this->assertSame('s3', $plan->disk);
        $this->assertSame('organizations/org-bem-ft/logo.png', $plan->storagePath);
    }

    public function test_it_rejects_invalid_logo_mime_type(): void
    {
        $plan = (new PlanOrganizationLogoUploadAction)->execute(
            new OrganizationLogoUploadData(
                organizationId: 'org-bem-ft',
                originalName: 'logo.svg',
                mimeType: 'image/svg+xml',
                sizeInKilobytes: 12,
            ),
        );

        $this->assertFalse($plan->isValid);
        $this->assertNull($plan->storagePath);
        $this->assertContains('Organization logo must be a JPG, PNG, or WEBP image.', $plan->errors);
    }

    public function test_it_rejects_oversized_logo(): void
    {
        $plan = (new PlanOrganizationLogoUploadAction)->execute(
            new OrganizationLogoUploadData(
                organizationId: 'org-bem-ft',
                originalName: 'logo.webp',
                mimeType: 'image/webp',
                sizeInKilobytes: 2049,
            ),
        );

        $this->assertFalse($plan->isValid);
        $this->assertContains('Organization logo file size exceeds 2 MB.', $plan->errors);
    }
}
