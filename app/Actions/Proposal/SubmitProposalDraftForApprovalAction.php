<?php

declare(strict_types=1);

namespace App\Actions\Proposal;

use App\Actions\Approval\StartApprovalWorkflowAction;
use App\Actions\DocumentExport\PlanDocumentExportAction;
use App\Actions\Notification\QueueWhatsAppNotificationAction;
use App\Actions\Project\TransitionProjectStatusAction;
use App\Domain\DocumentExport\ExportDocumentType;
use App\Domain\DocumentExport\ExportFormat;
use App\Domain\Notification\NotificationEvent;
use App\Domain\Project\ProjectRole;
use App\Domain\Project\ProjectStatus;
use App\DTOs\DocumentExport\ExportRequestData;
use App\Jobs\GenerateDocumentExportJob;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final readonly class SubmitProposalDraftForApprovalAction
{
    public function __construct(
        private PlanDocumentExportAction $planDocumentExport,
        private TransitionProjectStatusAction $transitionProjectStatus,
        private QueueWhatsAppNotificationAction $queueWhatsAppNotification,
        private StartApprovalWorkflowAction $startApprovalWorkflow,
    ) {}

    /**
     * @return array{id: int, project_slug: string}
     *
     * @throws ValidationException
     */
    public function execute(int $actorUserId, int $proposalDraftId): array
    {
        return DB::transaction(function () use ($actorUserId, $proposalDraftId): array {
            $draft = DB::table('proposal_drafts')
                ->join('projects', 'projects.id', '=', 'proposal_drafts.project_id')
                ->join('organization_members', 'organization_members.organization_id', '=', 'projects.organization_id')
                ->leftJoin('project_members', function ($join) use ($actorUserId): void {
                    $join->on('project_members.project_id', '=', 'projects.id')
                        ->where('project_members.user_id', $actorUserId)
                        ->where('project_members.role', ProjectRole::ProjectLead->value);
                })
                ->where('proposal_drafts.id', $proposalDraftId)
                ->where('organization_members.user_id', $actorUserId)
                ->where(function ($query): void {
                    $query
                        ->whereIn('organization_members.role', ['organization_owner', 'organization_admin', 'secretary'])
                        ->orWhereNotNull('project_members.id');
                })
                ->select([
                    'proposal_drafts.id',
                    'proposal_drafts.project_id',
                    'proposal_drafts.title',
                    'proposal_drafts.status as draft_status',
                    'projects.organization_id',
                    'projects.slug as project_slug',
                    'projects.status as project_status',
                ])
                ->lockForUpdate()
                ->first();

            if ($draft === null) {
                throw new NotFoundHttpException('Proposal draft was not found for the active workspace.');
            }

            if ((string) $draft->draft_status !== 'draft') {
                throw ValidationException::withMessages([
                    'proposalDraft' => 'Only draft proposals can be submitted for approval.',
                ]);
            }

            try {
                $targetStatus = $this->transitionProjectStatus->execute(
                    ProjectStatus::from((string) $draft->project_status),
                    ProjectStatus::ProposalReview,
                );
            } catch (DomainException) {
                throw ValidationException::withMessages([
                    'proposalDraft' => 'Proposal can only be submitted before the proker moves past proposal review.',
                ]);
            }

            $now = now();

            DB::table('proposal_drafts')
                ->where('id', $proposalDraftId)
                ->update([
                    'status' => 'submitted',
                    'updated_at' => $now,
                ]);

            DB::table('projects')
                ->where('id', (int) $draft->project_id)
                ->update([
                    'status' => $targetStatus->value,
                    'updated_at' => $now,
                ]);

            $plan = $this->planDocumentExport->execute(new ExportRequestData(
                documentId: 'proposal-draft-'.$draft->id,
                documentTitle: (string) $draft->title,
                documentType: ExportDocumentType::Proposal,
                format: ExportFormat::Pdf,
                requestedBy: (string) $actorUserId,
            ));

            DB::table('document_exports')->updateOrInsert(
                ['output_path' => $plan->outputPath],
                [
                    'organization_id' => (int) $draft->organization_id,
                    'project_id' => (int) $draft->project_id,
                    'requested_by_user_id' => $actorUserId,
                    'document_title' => (string) $draft->title,
                    'document_type' => ExportDocumentType::Proposal->value,
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

            $this->queueWhatsAppNotification->execute(
                organizationId: (int) $draft->organization_id,
                event: NotificationEvent::ProposalReviewRequested,
                userIds: $this->reviewerUserIds((int) $draft->organization_id, ['organization_owner', 'organization_admin', 'secretary']),
                messageType: NotificationEvent::ProposalReviewRequested->value,
                message: sprintf('Review proposal Prokerin: %s menunggu pengecekan.', (string) $draft->title),
            );

            if ($this->hasActiveWorkflow((int) $draft->organization_id, 'proposal')) {
                $this->startApprovalWorkflow->execute(
                    organizationId: (int) $draft->organization_id,
                    workflowType: 'proposal',
                    subjectType: 'proposal_draft',
                    subjectId: $proposalDraftId,
                    submittedByUserId: $actorUserId,
                );
            }

            return [
                'id' => $proposalDraftId,
                'project_slug' => (string) $draft->project_slug,
            ];
        });
    }

    /**
     * @param  array<int, string>  $roles
     * @return array<int, int>
     */
    private function reviewerUserIds(int $organizationId, array $roles): array
    {
        return DB::table('organization_members')
            ->where('organization_id', $organizationId)
            ->whereIn('role', $roles)
            ->pluck('user_id')
            ->map(static fn (int|string $id): int => (int) $id)
            ->all();
    }

    private function hasActiveWorkflow(int $organizationId, string $workflowType): bool
    {
        return DB::table('approval_workflow_definitions')
            ->where('organization_id', $organizationId)
            ->where('workflow_type', $workflowType)
            ->where('is_active', true)
            ->exists();
    }
}
