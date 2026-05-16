<?php

declare(strict_types=1);

namespace App\Actions\EventRegistration;

use App\Actions\DocumentExport\PlanDocumentExportAction;
use App\Domain\DocumentExport\ExportDocumentType;
use App\Domain\DocumentExport\ExportFormat;
use App\DTOs\DocumentExport\ExportRequestData;
use App\Jobs\GenerateDocumentExportJob;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final readonly class QueueEventRegistrationPdfExportAction
{
    public function __construct(private PlanDocumentExportAction $planDocumentExport) {}

    /**
     * @return array{id: int, output_path: string}
     *
     * @throws AuthorizationException
     */
    public function execute(int $actorUserId, int $projectId): array
    {
        $project = DB::table('projects')
            ->join('organizations', 'organizations.id', '=', 'projects.organization_id')
            ->leftJoin('organization_members', function ($join) use ($actorUserId): void {
                $join->on('organization_members.organization_id', '=', 'projects.organization_id')
                    ->where('organization_members.user_id', $actorUserId);
            })
            ->leftJoin('project_members', function ($join) use ($actorUserId): void {
                $join->on('project_members.project_id', '=', 'projects.id')
                    ->where('project_members.user_id', $actorUserId);
            })
            ->where('projects.id', $projectId)
            ->first([
                'projects.id',
                'projects.organization_id',
                'projects.name',
                'organizations.name as organization_name',
                'organization_members.role as organization_role',
                'project_members.role as project_role',
            ]);

        if ($project === null) {
            throw new NotFoundHttpException('Event was not found for this workspace.');
        }

        $canExport = in_array((string) $project->organization_role, ['organization_owner', 'organization_admin', 'secretary'], true)
            || in_array((string) $project->project_role, ['project_lead'], true);

        if (! $canExport) {
            throw new AuthorizationException('You are not allowed to export event registrations.');
        }

        $title = sprintf('Daftar Peserta %s', (string) $project->name);
        $plan = $this->planDocumentExport->execute(new ExportRequestData(
            documentId: 'event-registration-'.$project->id,
            documentTitle: $title,
            documentType: ExportDocumentType::EventRegistration,
            format: ExportFormat::Pdf,
            requestedBy: (string) $actorUserId,
        ));
        $now = now();

        DB::table('document_exports')->updateOrInsert(
            ['output_path' => $plan->outputPath],
            [
                'organization_id' => (int) $project->organization_id,
                'project_id' => (int) $project->id,
                'requested_by_user_id' => $actorUserId,
                'document_title' => $title,
                'document_type' => ExportDocumentType::EventRegistration->value,
                'format' => ExportFormat::Pdf->value,
                'queue_name' => $plan->queueName,
                'engine' => $plan->engine,
                'storage_disk' => $plan->storageDisk,
                'status' => 'queued',
                'updated_at' => $now,
                'created_at' => $now,
            ],
        );

        $documentExportId = (int) DB::table('document_exports')
            ->where('output_path', $plan->outputPath)
            ->value('id');

        GenerateDocumentExportJob::dispatch($documentExportId)
            ->onQueue($plan->queueName)
            ->afterCommit();

        return [
            'id' => $documentExportId,
            'output_path' => $plan->outputPath,
        ];
    }
}
