<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Actions\DocumentExport\GenerateDocumentExportContentAction;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

final class GenerateDocumentExportJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $documentExportId,
    ) {}

    public function handle(): void
    {
        $export = DB::table('document_exports')
            ->where('id', $this->documentExportId)
            ->first();

        if ($export === null || (string) $export->status === 'completed') {
            return;
        }

        $now = now();

        DB::table('document_exports')
            ->where('id', $this->documentExportId)
            ->update([
                'status' => 'processing',
                'updated_at' => $now,
            ]);

        $content = app(GenerateDocumentExportContentAction::class)->execute($export);

        Storage::disk((string) $export->storage_disk)->put((string) $export->output_path, $content);

        DB::table('document_exports')
            ->where('id', $this->documentExportId)
            ->update([
                'status' => 'completed',
                'updated_at' => now(),
            ]);
    }

    public function failed(Throwable $exception): void
    {
        DB::table('document_exports')
            ->where('id', $this->documentExportId)
            ->update([
                'status' => 'failed',
                'updated_at' => now(),
            ]);
    }
}
