<?php

declare(strict_types=1);

namespace App\Actions\Meeting;

use App\Actions\DocumentExport\PlanDocumentExportAction;
use App\Domain\DocumentExport\ExportDocumentType;
use App\Domain\DocumentExport\ExportFormat;
use App\DTOs\DocumentExport\ExportRequestData;
use App\Jobs\GenerateDocumentExportJob;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final readonly class QueueMeetingMinutesExportAction
{
    public function __construct(private PlanDocumentExportAction $planDocumentExport) {}

    /**
     * @return array{id: int, output_path: string}
     *
     * @throws AuthorizationException
     */
    public function execute(int $actorUserId, int $meetingId, string $format): array
    {
        if (! in_array($format, ['pdf', 'docx'], true)) {
            throw new AuthorizationException('Format export tidak didukung.');
        }

        $meeting = DB::table('meetings')
            ->leftJoin('organizations', 'organizations.id', '=', 'meetings.organization_id')
            ->leftJoin('meeting_minutes', 'meeting_minutes.meeting_id', '=', 'meetings.id')
            ->where('meetings.id', $meetingId)
            ->first([
                'meetings.id',
                'meetings.title',
                'meetings.project_id',
                'meetings.organization_id',
                'organizations.name as organization_name',
                'meeting_minutes.published_at',
            ]);

        if ($meeting === null) {
            throw new NotFoundHttpException('Meeting not found.');
        }

        if ($meeting->published_at === null) {
            throw new AuthorizationException('Notulen belum dipublikasikan, tidak bisa di-export.');
        }

        $role = (string) DB::table('organization_members')
            ->where('user_id', $actorUserId)
            ->where('organization_id', $meeting->organization_id)
            ->value('role');

        $allowed = ['organization_owner', 'organization_admin', 'secretary', 'treasurer', 'member', 'viewer'];

        if (! in_array($role, $allowed, true)) {
            throw new AuthorizationException('Tidak memiliki akses ke organisasi ini.');
        }

        $title = sprintf('Notulen %s', (string) $meeting->title);

        $exportFormat = $format === 'pdf' ? ExportFormat::Pdf : ExportFormat::Docx;

        $plan = $this->planDocumentExport->execute(new ExportRequestData(
            documentId: 'meeting-'.$meeting->id,
            documentTitle: $title,
            documentType: ExportDocumentType::MeetingMinutes,
            format: $exportFormat,
            requestedBy: (string) $actorUserId,
        ));

        $now = now();

        DB::table('document_exports')->updateOrInsert(
            ['output_path' => $plan->outputPath],
            [
                'organization_id' => (int) $meeting->organization_id,
                'project_id' => $meeting->project_id === null ? null : (int) $meeting->project_id,
                'requested_by_user_id' => $actorUserId,
                'document_title' => $title,
                'document_type' => ExportDocumentType::MeetingMinutes->value,
                'format' => $exportFormat->value,
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
