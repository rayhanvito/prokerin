<?php

declare(strict_types=1);

namespace App\Actions\Workspace;

use Illuminate\Support\Facades\DB;

final class GetExportQueuePayloadAction
{
    /**
     * @return array<int, array{document: string, type: string, requested: string, queue: string, status: string, downloadUrl: string|null, plan: array<string, mixed>}>
     */
    public function execute(int $actorUserId): array
    {
        return DB::table('document_exports')
            ->join('organization_members', 'organization_members.organization_id', '=', 'document_exports.organization_id')
            ->leftJoin('users', 'users.id', '=', 'document_exports.requested_by_user_id')
            ->where('organization_members.user_id', $actorUserId)
            ->select('document_exports.*', 'users.name as requested_by_name')
            ->orderBy('document_exports.id')
            ->get()
            ->map(static fn (object $export): array => [
                'document' => (string) $export->document_title,
                'type' => strtoupper((string) $export->format),
                'requested' => (string) ($export->requested_by_name ?? 'System'),
                'queue' => (string) $export->queue_name,
                'status' => (string) $export->status,
                'downloadUrl' => (string) $export->status === 'completed'
                    ? route('reports.exports.download', ['documentExport' => $export->id])
                    : null,
                'plan' => [
                    'queueName' => (string) $export->queue_name,
                    'engine' => (string) $export->engine,
                    'storageDisk' => (string) $export->storage_disk,
                    'outputPath' => (string) $export->output_path,
                    'shouldQueue' => true,
                ],
            ])
            ->all();
    }
}
