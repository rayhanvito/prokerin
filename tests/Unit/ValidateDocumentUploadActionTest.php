<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Actions\Document\ValidateDocumentUploadAction;
use App\Domain\Document\DocumentVisibility;
use App\DTOs\Document\DocumentUploadCandidateData;
use PHPUnit\Framework\TestCase;

final class ValidateDocumentUploadActionTest extends TestCase
{
    public function test_it_accepts_valid_private_document_upload(): void
    {
        $result = (new ValidateDocumentUploadAction)->execute(
            new DocumentUploadCandidateData(
                originalName: 'proposal.pdf',
                mimeType: 'application/pdf',
                sizeInKilobytes: 2048,
                visibility: DocumentVisibility::Private,
            ),
        );

        $this->assertTrue($result->isValid);
        $this->assertSame([], $result->errors);
        $this->assertTrue($result->requiresSignedUrl);
    }

    public function test_it_rejects_disallowed_mime_type(): void
    {
        $result = (new ValidateDocumentUploadAction)->execute(
            new DocumentUploadCandidateData(
                originalName: 'script.php',
                mimeType: 'application/x-php',
                sizeInKilobytes: 12,
                visibility: DocumentVisibility::Restricted,
            ),
        );

        $this->assertFalse($result->isValid);
        $this->assertContains('Document MIME type is not allowed.', $result->errors);
    }

    public function test_it_rejects_oversized_upload(): void
    {
        $result = (new ValidateDocumentUploadAction)->execute(
            new DocumentUploadCandidateData(
                originalName: 'documentation.zip',
                mimeType: 'application/zip',
                sizeInKilobytes: 10241,
                visibility: DocumentVisibility::Committee,
            ),
        );

        $this->assertFalse($result->isValid);
        $this->assertContains('Document file size exceeds 10 MB.', $result->errors);
        $this->assertFalse($result->requiresSignedUrl);
    }
}
