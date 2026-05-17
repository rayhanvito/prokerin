<?php

declare(strict_types=1);

namespace App\Actions\Report;

use App\Actions\DocumentExport\PlanDocumentExportAction;
use App\Domain\DocumentExport\ExportDocumentType;
use App\Domain\DocumentExport\ExportFormat;
use App\Domain\Organization\OrganizationRole;
use App\Domain\Project\ProjectStatus;
use App\DTOs\DocumentExport\ExportRequestData;
use App\Jobs\GenerateDocumentExportJob;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final readonly class QueueLpjExportAction
{
    public function __construct(
        private PlanDocumentExportAction $planDocumentExport,
    ) {}

    /**
     * @return array{id: int, output_path: string}
     *
     * @throws AuthorizationException
     */
    public function execute(int $actorUserId, int $projectId): array
    {
        $project = DB::table('projects')
            ->join('organization_members', 'organization_members.organization_id', '=', 'projects.organization_id')
            ->where('projects.id', $projectId)
            ->where('organization_members.user_id', $actorUserId)
            ->whereIn('organization_members.role', [
                OrganizationRole::Owner->value,
                OrganizationRole::Admin->value,
                OrganizationRole::Secretary->value,
            ])
            ->first([
                'projects.id',
                'projects.name',
                'projects.organization_id',
                'projects.status',
            ]);

        if ($project === null) {
            throw new NotFoundHttpException('Project was not found for LPJ export.');
        }

        if ((string) $project->status !== ProjectStatus::Completed->value) {
            throw new AuthorizationException('LPJ hanya bisa di-export setelah proker completed.');
        }

        $title = sprintf('LPJ %s', (string) $project->name);
        $plan = $this->planDocumentExport->execute(new ExportRequestData(
            documentId: 'lpj-project-'.$project->id,
            documentTitle: $title,
            documentType: ExportDocumentType::Lpj,
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
                'document_type' => ExportDocumentType::Lpj->value,
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
