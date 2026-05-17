<?php

declare(strict_types=1);

namespace App\Actions\DocumentExport;

use App\Actions\SuperAdmin\LogActivityAction;
use App\Jobs\GenerateDocumentExportJob;
use App\Models\DocumentExport;
use Illuminate\Support\Facades\DB;

final class RetryDocumentExportAction
{
    public function __construct(
        private LogActivityAction $logActivity,
    ) {}

    public function execute(DocumentExport $documentExport, int $actorUserId): void
    {
        DB::table('document_exports')
            ->where('id', $documentExport->id)
            ->update([
                'status' => 'queued',
                'updated_at' => now(),
            ]);

        GenerateDocumentExportJob::dispatch((int) $documentExport->id)
            ->onQueue((string) ($documentExport->queue_name ?? 'exports'))
            ->afterCommit();

        $this->logActivity->execute('document_export.retry', $documentExport, [
            'document_title' => (string) $documentExport->document_title,
            'document_type' => (string) $documentExport->document_type,
        ], $actorUserId);
    }
}
