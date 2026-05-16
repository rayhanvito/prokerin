<?php

declare(strict_types=1);

namespace App\Actions\DocumentExport;

use App\DTOs\DocumentExport\ExportQueuePlanData;
use App\DTOs\DocumentExport\ExportRequestData;

final class PlanDocumentExportAction
{
    public function execute(ExportRequestData $request): ExportQueuePlanData
    {
        return new ExportQueuePlanData(
            queueName: 'exports',
            engine: $request->format->engine(),
            storageDisk: 's3',
            outputPath: $this->buildOutputPath($request),
            shouldQueue: true,
        );
    }

    private function buildOutputPath(ExportRequestData $request): string
    {
        $safeTitle = strtolower((string) preg_replace('/[^A-Za-z0-9]+/', '-', $request->documentTitle));
        $safeTitle = trim($safeTitle, '-');

        return sprintf(
            'exports/%s/%s/%s.%s',
            $request->documentType->value,
            $request->documentId,
            $safeTitle,
            $request->format->value,
        );
    }
}
