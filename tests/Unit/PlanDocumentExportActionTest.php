<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Actions\DocumentExport\PlanDocumentExportAction;
use App\Domain\DocumentExport\ExportDocumentType;
use App\Domain\DocumentExport\ExportFormat;
use App\DTOs\DocumentExport\ExportRequestData;
use PHPUnit\Framework\TestCase;

final class PlanDocumentExportActionTest extends TestCase
{
    public function test_it_plans_pdf_export_with_browsershot(): void
    {
        $plan = (new PlanDocumentExportAction)->execute(
            new ExportRequestData(
                documentId: 'proposal-001',
                documentTitle: 'Proposal Seminar Karier Digital',
                documentType: ExportDocumentType::Proposal,
                format: ExportFormat::Pdf,
                requestedBy: 'Secretary',
            ),
        );

        $this->assertSame('exports', $plan->queueName);
        $this->assertSame('browsershot', $plan->engine);
        $this->assertSame('s3', $plan->storageDisk);
        $this->assertSame('exports/proposal/proposal-001/proposal-seminar-karier-digital.pdf', $plan->outputPath);
        $this->assertTrue($plan->shouldQueue);
    }

    public function test_it_plans_docx_export_with_phpword(): void
    {
        $plan = (new PlanDocumentExportAction)->execute(
            new ExportRequestData(
                documentId: 'lpj-001',
                documentTitle: 'LPJ Workshop UI/UX',
                documentType: ExportDocumentType::Lpj,
                format: ExportFormat::Docx,
                requestedBy: 'Treasurer',
            ),
        );

        $this->assertSame('phpword', $plan->engine);
        $this->assertSame('exports/lpj/lpj-001/lpj-workshop-ui-ux.docx', $plan->outputPath);
    }

    public function test_export_plan_serializes_for_queue_payload(): void
    {
        $payload = (new PlanDocumentExportAction)->execute(
            new ExportRequestData(
                documentId: 'proposal-002',
                documentTitle: 'Proposal Makrab 2026',
                documentType: ExportDocumentType::Proposal,
                format: ExportFormat::Pdf,
                requestedBy: 'Project Lead',
            ),
        )->toArray();

        $this->assertSame('exports', $payload['queueName']);
        $this->assertSame('browsershot', $payload['engine']);
        $this->assertTrue($payload['shouldQueue']);
    }
}
