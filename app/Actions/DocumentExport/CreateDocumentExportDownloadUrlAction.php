<?php

declare(strict_types=1);

namespace App\Actions\DocumentExport;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class CreateDocumentExportDownloadUrlAction
{
    /**
     * @throws AuthorizationException
     */
    public function execute(int $actorUserId, int $documentExportId): string
    {
        $export = DB::table('document_exports')
            ->join('organization_members', 'organization_members.organization_id', '=', 'document_exports.organization_id')
            ->where('document_exports.id', $documentExportId)
            ->where('organization_members.user_id', $actorUserId)
            ->select('document_exports.*')
            ->first();

        if ($export === null) {
            throw new NotFoundHttpException('Document export was not found for the active workspace.');
        }

        if ((string) $export->status !== 'completed') {
            throw new ConflictHttpException('Document export is not ready for download.');
        }

        return Storage::disk((string) $export->storage_disk)->temporaryUrl(
            (string) $export->output_path,
            now()->addMinutes(15),
            ['ResponseContentDisposition' => 'attachment; filename="'.$this->downloadName($export).'"'],
        );
    }

    private function downloadName(object $export): string
    {
        $safeTitle = strtolower((string) preg_replace('/[^A-Za-z0-9]+/', '-', (string) $export->document_title));
        $safeTitle = trim($safeTitle, '-') ?: 'prokerin-export';

        return sprintf('%s.%s', $safeTitle, (string) $export->format);
    }
}
