<?php

declare(strict_types=1);

namespace App\Actions\Handover;

use App\Actions\DocumentExport\PlanDocumentExportAction;
use App\Domain\DocumentExport\ExportDocumentType;
use App\Domain\DocumentExport\ExportFormat;
use App\DTOs\DocumentExport\ExportRequestData;
use App\Jobs\GenerateDocumentExportJob;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final readonly class QueueHandoverPackageExportAction
{
    public function __construct(
        private PlanDocumentExportAction $planDocumentExport,
    ) {}

    /**
     * @return array{id: int, output_path: string}
     *
     * @throws ValidationException
     */
    public function execute(int $actorUserId, int $handoverPackageId): array
    {
        return DB::transaction(function () use ($actorUserId, $handoverPackageId): array {
            $package = DB::table('handover_packages')
                ->join('organizations', 'organizations.id', '=', 'handover_packages.organization_id')
                ->join('organization_members', 'organization_members.organization_id', '=', 'handover_packages.organization_id')
                ->leftJoin('organization_periods', 'organization_periods.id', '=', 'handover_packages.from_period_id')
                ->where('handover_packages.id', $handoverPackageId)
                ->where('organization_members.user_id', $actorUserId)
                ->whereIn('organization_members.role', ['organization_owner', 'organization_admin'])
                ->select([
                    'handover_packages.id',
                    'handover_packages.organization_id',
                    'handover_packages.status',
                    'organizations.name as organization_name',
                    'organization_periods.name as period_name',
                ])
                ->lockForUpdate()
                ->first();

            if ($package === null) {
                throw new NotFoundHttpException('Handover package was not found for the active workspace.');
            }

            if ((string) $package->status !== 'accepted') {
                throw ValidationException::withMessages([
                    'handoverPackage' => 'Paket handover harus diterima sebelum diarsipkan sebagai PDF.',
                ]);
            }

            $title = sprintf(
                'Paket Handover %s %s',
                (string) $package->organization_name,
                (string) ($package->period_name ?? ''),
            );

            $plan = $this->planDocumentExport->execute(new ExportRequestData(
                documentId: 'handover-package-'.$package->id,
                documentTitle: trim($title),
                documentType: ExportDocumentType::Handover,
                format: ExportFormat::Pdf,
                requestedBy: (string) $actorUserId,
            ));

            $now = now();

            DB::table('document_exports')->updateOrInsert(
                ['output_path' => $plan->outputPath],
                [
                    'organization_id' => (int) $package->organization_id,
                    'project_id' => null,
                    'requested_by_user_id' => $actorUserId,
                    'document_title' => trim($title),
                    'document_type' => ExportDocumentType::Handover->value,
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
        });
    }
}
