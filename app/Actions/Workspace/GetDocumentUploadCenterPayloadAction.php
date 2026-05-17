<?php

declare(strict_types=1);

namespace App\Actions\Workspace;

use App\Domain\Document\DocumentVisibility;
use App\Support\Roles;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

final readonly class GetDocumentUploadCenterPayloadAction
{
    public function __construct(
        private GetActiveOrganizationContextAction $activeOrganizationContext,
    ) {}

    /**
     * @return array{documents: array<int, array{id: int, name: string, folder: string, owner: string, visibility: string, status: string, downloadHref: string}>, projects: array<int, array{id: int, name: string}>}
     */
    public function execute(int $actorUserId, ?int $preferredOrganizationId = null): array
    {
        $context = $this->activeOrganizationContext->execute($actorUserId, $preferredOrganizationId);

        return [
            'documents' => $this->documents($context->organizationId, $actorUserId, $context->role),
            'projects' => $this->projects($context->organizationId),
        ];
    }

    /**
     * @return array<int, array{id: int, name: string, folder: string, owner: string, visibility: string, status: string, downloadHref: string}>
     */
    private function documents(int $organizationId, int $actorUserId, string $role): array
    {
        $documents = DB::table('documents')
            ->leftJoin('users', 'users.id', '=', 'documents.owner_user_id')
            ->leftJoin('project_members', function ($join) use ($actorUserId): void {
                $join->on('project_members.project_id', '=', 'documents.project_id')
                    ->where('project_members.user_id', $actorUserId);
            })
            ->where('documents.organization_id', $organizationId)
            ->where(fn ($query) => $this->applyVisibilityFilter($query, $actorUserId, $role))
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

        return $documents;
    }

    private function applyVisibilityFilter(Builder $query, int $actorUserId, string $role): void
    {
        $query
            ->where('documents.owner_user_id', $actorUserId)
            ->orWhere('documents.visibility', DocumentVisibility::Public->value)
            ->orWhere(function ($committeeQuery): void {
                $committeeQuery
                    ->where('documents.visibility', DocumentVisibility::Committee->value)
                    ->whereNotNull('project_members.id');
            });

        if (in_array($role, Roles::ORGANIZATION_MANAGERS, true)) {
            $query->orWhere('documents.visibility', DocumentVisibility::Private->value);
        }

        if (in_array($role, Roles::FINANCE_VIEWERS, true)) {
            $query->orWhere('documents.visibility', DocumentVisibility::Restricted->value);
        }

        if (in_array($role, Roles::SECRETARY_AND_UP, true)) {
            $query->orWhere('documents.visibility', DocumentVisibility::Committee->value);
        }
    }

    /**
     * @return array<int, array{id: int, name: string}>
     */
    private function projects(int $organizationId): array
    {
        return DB::table('projects')
            ->where('organization_id', $organizationId)
            ->where('status', '!=', 'archived')
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(static fn (object $project): array => [
                'id' => (int) $project->id,
                'name' => (string) $project->name,
            ])
            ->all();
    }
}
