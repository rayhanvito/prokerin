<?php

declare(strict_types=1);

namespace App\Actions\Workspace;

use Illuminate\Support\Facades\DB;

final class GetDocumentUploadCenterPayloadAction
{
    /**
     * @return array<int, array{id: int, name: string, folder: string, owner: string, visibility: string, status: string, downloadHref: string}>
     */
    public function execute(int $actorUserId): array
    {
        return DB::table('documents')
            ->join('organization_members', 'organization_members.organization_id', '=', 'documents.organization_id')
            ->leftJoin('users', 'users.id', '=', 'documents.owner_user_id')
            ->where('organization_members.user_id', $actorUserId)
            ->select([
                'documents.id',
                'documents.name',
                'documents.folder',
                'documents.visibility',
                'documents.status',
                'users.name as owner_name',
            ])
            ->orderByDesc('documents.updated_at')
            ->orderByDesc('documents.id')
            ->get()
            ->map(static fn (object $document): array => [
                'id' => (int) $document->id,
                'name' => (string) $document->name,
                'folder' => (string) $document->folder,
                'owner' => (string) ($document->owner_name ?? 'System'),
                'visibility' => (string) $document->visibility,
                'status' => (string) $document->status,
                'downloadHref' => route('documents.download', ['document' => (int) $document->id]),
            ])
            ->all();
    }
}
