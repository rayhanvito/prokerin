<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Actions\Document\PlanDocumentDownloadAction;
use App\Domain\Document\DocumentVisibility;
use App\DTOs\Document\DocumentDownloadRequestData;
use DomainException;
use PHPUnit\Framework\TestCase;

final class PlanDocumentDownloadActionTest extends TestCase
{
    public function test_it_requires_signed_url_for_private_document(): void
    {
        $plan = (new PlanDocumentDownloadAction)->execute(
            new DocumentDownloadRequestData(
                storagePath: 'documents/proposal-v2.pdf',
                originalName: 'proposal-v2.pdf',
                visibility: DocumentVisibility::Private,
            ),
        );

        $this->assertSame('s3', $plan->disk);
        $this->assertTrue($plan->requiresSignedUrl);
        $this->assertSame(10, $plan->expiresInMinutes);
    }

    public function test_committee_document_can_skip_signed_url_requirement(): void
    {
        $plan = (new PlanDocumentDownloadAction)->execute(
            new DocumentDownloadRequestData(
                storagePath: 'documents/documentation-day-1.zip',
                originalName: 'documentation-day-1.zip',
                visibility: DocumentVisibility::Committee,
            ),
        );

        $this->assertFalse($plan->requiresSignedUrl);
        $this->assertSame(0, $plan->expiresInMinutes);
    }

    public function test_it_rejects_unsafe_storage_path(): void
    {
        $this->expectException(DomainException::class);

        (new PlanDocumentDownloadAction)->execute(
            new DocumentDownloadRequestData(
                storagePath: '../.env',
                originalName: '.env',
                visibility: DocumentVisibility::Private,
            ),
        );
    }
}
