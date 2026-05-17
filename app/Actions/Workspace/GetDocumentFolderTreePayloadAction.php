<?php

declare(strict_types=1);

namespace App\Actions\Workspace;

use App\Domain\Document\DocumentVisibility;
use App\Support\Roles;
use Illuminate\Support\Facades\DB;

final readonly class GetDocumentFolderTreePayloadAction
{
    public function __construct(
        private GetActiveOrganizationContextAction $activeOrganizationContext,
    ) {}

    /**
     * @return array<int, array{name: string, files: int, access: string, documents: array<int, array{id: int, name: string, visibility: string, status: string, downloadHref: string}>}>
     */
    public function execute(int $actorUserId, ?int $preferredOrganizationId = null): array
    {
        $context = $this->activeOrganizationContext->execute($actorUserId, $preferredOrganizationId);
        $canViewPrivate = in_array($context->role, Roles::ORGANIZATION_MANAGERS, true);
        $canViewRestricted = in_array($context->role, Roles::FINANCE_VIEWERS, true);
        $canViewCommittee = in_array($context->role, Roles::SECRETARY_AND_UP, true);

        $documents = DB::table('documents')
            ->leftJoin('project_members', function ($join) use ($actorUserId): void {
                $join->on('project_members.project_id', '=', 'documents.project_id')
                    ->where('project_members.user_id', $actorUserId);
            })
            ->where('documents.organization_id', $context->organizationId)
            ->where(function ($query) use ($actorUserId, $canViewCommittee, $canViewPrivate, $canViewRestricted): void {
                $query
                    ->where('documents.owner_user_id', $actorUserId)
                    ->orWhere('documents.visibility', DocumentVisibility::Public->value)
                    ->orWhere(function ($committeeQuery): void {
                        $committeeQuery
                            ->where('documents.visibility', DocumentVisibility::Committee->value)
                            ->whereNotNull('project_members.id');
                    });

                if ($canViewPrivate) {
                    $query->orWhere('documents.visibility', DocumentVisibility::Private->value);
                }

                if ($canViewRestricted) {
                    $query->orWhere('documents.visibility', DocumentVisibility::Restricted->value);
                }

                if ($canViewCommittee) {
                    $query->orWhere('documents.visibility', DocumentVisibility::Committee->value);
                }
            })
            ->orderBy('documents.folder')
            ->orderByDesc('documents.updated_at')
            ->get(['documents.id', 'documents.name', 'documents.folder', 'documents.visibility', 'documents.status']);

        return $documents
            ->groupBy(static fn (object $document): string => (string) $document->folder)
            ->map(static function ($folderDocuments, string $folder): array {
                $visibilityCounts = $folderDocuments->countBy(static fn (object $document): string => (string) $document->visibility);
                $access = (string) $visibilityCounts->sortDesc()->keys()->first();

                return [
                    'name' => $folder,
                    'files' => $folderDocuments->count(),
                    'access' => $access,
                    'documents' => $folderDocuments->map(static fn (object $document): array => [
                        'id' => (int) $document->id,
                        'name' => (string) $document->name,
                        'visibility' => (string) $document->visibility,
                        'status' => (string) $document->status,
                        'downloadHref' => route('documents.download', ['document' => (int) $document->id]),
                    ])->values()->all(),
                ];
            })
            ->values()
            ->all();
    }
}
