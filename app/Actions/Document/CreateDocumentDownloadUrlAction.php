<?php

declare(strict_types=1);

namespace App\Actions\Document;

use App\Domain\Document\DocumentVisibility;
use App\Domain\Organization\OrganizationRole;
use App\DTOs\Document\DocumentDownloadRequestData;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final readonly class CreateDocumentDownloadUrlAction
{
    public function __construct(
        private PlanDocumentDownloadAction $planDocumentDownload,
    ) {}

    /**
     * @throws AuthorizationException
     */
    public function execute(int $actorUserId, int $documentId): string
    {
        $document = DB::table('documents')
            ->join('organization_members', 'organization_members.organization_id', '=', 'documents.organization_id')
            ->where('documents.id', $documentId)
            ->where('organization_members.user_id', $actorUserId)
            ->select([
                'documents.id',
                'documents.name',
                'documents.storage_path',
                'documents.visibility',
                'documents.owner_user_id',
                'organization_members.role',
            ])
            ->first();

        if ($document === null) {
            throw new NotFoundHttpException('Document was not found for the active workspace.');
        }

        $visibility = DocumentVisibility::from((string) $document->visibility);

        if (! $this->canDownload($visibility, (string) $document->role, (int) $document->owner_user_id, $actorUserId)) {
            throw new AuthorizationException('You are not allowed to download this document.');
        }

        $plan = $this->planDocumentDownload->execute(new DocumentDownloadRequestData(
            storagePath: (string) $document->storage_path,
            originalName: (string) $document->name,
            visibility: $visibility,
        ));

        if ($plan->requiresSignedUrl) {
            return Storage::disk($plan->disk)->temporaryUrl(
                $plan->path,
                now()->addMinutes($plan->expiresInMinutes),
                ['ResponseContentDisposition' => 'attachment; filename="'.$plan->downloadName.'"'],
            );
        }

        return Storage::disk($plan->disk)->url($plan->path);
    }

    private function canDownload(
        DocumentVisibility $visibility,
        string $role,
        int $ownerUserId,
        int $actorUserId,
    ): bool {
        if ($ownerUserId === $actorUserId) {
            return true;
        }

        return match ($visibility) {
            DocumentVisibility::Private => in_array($role, [
                OrganizationRole::Owner->value,
                OrganizationRole::Admin->value,
                OrganizationRole::Secretary->value,
            ], true),
            DocumentVisibility::Restricted => in_array($role, [
                OrganizationRole::Owner->value,
                OrganizationRole::Admin->value,
                OrganizationRole::Treasurer->value,
            ], true),
            DocumentVisibility::Committee => true,
        };
    }
}
