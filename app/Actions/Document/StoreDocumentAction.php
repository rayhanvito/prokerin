<?php

declare(strict_types=1);

namespace App\Actions\Document;

use App\Actions\Workspace\GetActiveOrganizationContextAction;
use App\Domain\Document\DocumentVisibility;
use App\DTOs\Document\DocumentUploadCandidateData;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpException;

final readonly class StoreDocumentAction
{
    public function __construct(
        private GetActiveOrganizationContextAction $activeOrganizationContext,
        private ValidateDocumentUploadAction $validateDocumentUpload,
    ) {}

    /**
     * @throws AuthorizationException
     */
    public function execute(
        int $actorUserId,
        UploadedFile $file,
        string $folder,
        DocumentVisibility $visibility,
        ?int $projectId,
        ?int $preferredOrganizationId = null,
    ): int {
        $context = $this->activeOrganizationContext->execute($actorUserId, $preferredOrganizationId);
        $this->ensureProjectBelongsToOrganization($projectId, $context->organizationId);

        $validation = $this->validateDocumentUpload->execute(new DocumentUploadCandidateData(
            originalName: $file->getClientOriginalName(),
            mimeType: (string) ($file->getMimeType() ?? $file->getClientMimeType()),
            sizeInKilobytes: (int) ceil($file->getSize() / 1024),
            visibility: $visibility,
        ));

        if (! $validation->isValid) {
            throw new HttpException(422, implode(' ', $validation->errors));
        }

        $safeName = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $extension = strtolower((string) $file->getClientOriginalExtension());
        $storedName = sprintf('%s-%s.%s', now()->format('YmdHis'), $safeName !== '' ? $safeName : 'document', $extension);
        $path = sprintf(
            'documents/%d/%s/%s',
            $context->organizationId,
            Str::slug($folder) ?: 'general',
            $storedName,
        );

        Storage::disk('s3')->putFileAs(dirname($path), $file, basename($path), ['visibility' => 'private']);

        return (int) DB::table('documents')->insertGetId([
            'organization_id' => $context->organizationId,
            'project_id' => $projectId,
            'owner_user_id' => $actorUserId,
            'name' => $file->getClientOriginalName(),
            'folder' => $folder,
            'storage_path' => $path,
            'mime_type' => (string) ($file->getMimeType() ?? $file->getClientMimeType()),
            'size_kb' => (int) ceil($file->getSize() / 1024),
            'visibility' => $visibility->value,
            'status' => 'uploaded',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * @throws AuthorizationException
     */
    private function ensureProjectBelongsToOrganization(?int $projectId, int $organizationId): void
    {
        if ($projectId === null) {
            return;
        }

        $belongsToActiveOrganization = DB::table('projects')
            ->where('id', $projectId)
            ->where('organization_id', $organizationId)
            ->exists();

        if (! $belongsToActiveOrganization) {
            throw new AuthorizationException('Project is not available in the active organization.');
        }
    }
}
