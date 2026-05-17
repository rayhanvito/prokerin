<?php

declare(strict_types=1);

namespace App\Actions\Workspace;

use Illuminate\Support\Facades\DB;

final readonly class GetReportsOverviewPayloadAction
{
    public function __construct(
        private GetActiveOrganizationContextAction $activeOrganizationContext,
    ) {}

    /**
     * @return array{metrics: array<int, array{label: string, value: string, note: string}>, proposalStatuses: array<int, array{status: string, count: int}>, lpjStatuses: array<int, array{status: string, count: int}>, exportQueue: array<int, array{id: int, title: string, type: string, format: string, status: string, requestedBy: string, downloadUrl: string|null}>, recentProjects: array<int, array{id: int, name: string, status: string, proposalStatus: string|null, lpjRequired: int, lpjComplete: int}>}
     */
    public function execute(int $actorUserId, ?int $preferredOrganizationId = null): array
    {
        $context = $this->activeOrganizationContext->execute($actorUserId, $preferredOrganizationId);

        $proposalStatuses = DB::table('proposal_drafts')
            ->join('projects', 'projects.id', '=', 'proposal_drafts.project_id')
            ->where('projects.organization_id', $context->organizationId)
            ->select('proposal_drafts.status', DB::raw('count(*) as total'))
            ->groupBy('proposal_drafts.status')
            ->orderBy('proposal_drafts.status')
            ->get()
            ->map(static fn (object $status): array => [
                'status' => (string) $status->status,
                'count' => (int) $status->total,
            ])
            ->all();

        $lpjStatuses = DB::table('projects')
            ->where('organization_id', $context->organizationId)
            ->whereIn('status', ['running', 'lpj_review', 'completed'])
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->orderBy('status')
            ->get()
            ->map(static fn (object $status): array => [
                'status' => (string) $status->status,
                'count' => (int) $status->total,
            ])
            ->all();

        $exportQueue = DB::table('document_exports')
            ->leftJoin('users', 'users.id', '=', 'document_exports.requested_by_user_id')
            ->where('document_exports.organization_id', $context->organizationId)
            ->orderByDesc('document_exports.updated_at')
            ->orderByDesc('document_exports.id')
            ->limit(5)
            ->get([
                'document_exports.id',
                'document_exports.document_title',
                'document_exports.document_type',
                'document_exports.format',
                'document_exports.status',
                'users.name as requested_by_name',
            ])
            ->map(static fn (object $export): array => [
                'id' => (int) $export->id,
                'title' => (string) $export->document_title,
                'type' => (string) $export->document_type,
                'format' => (string) $export->format,
                'status' => (string) $export->status,
                'requestedBy' => (string) ($export->requested_by_name ?? 'System'),
                'downloadUrl' => (string) $export->status === 'completed'
                    ? route('reports.exports.download', ['documentExport' => (int) $export->id])
                    : null,
            ])
            ->all();

        return [
            'metrics' => [
                [
                    'label' => 'Proposal Draft',
                    'value' => (string) array_sum(array_column($proposalStatuses, 'count')),
                    'note' => 'Dokumen proposal di organisasi aktif',
                ],
                [
                    'label' => 'LPJ Aktif',
                    'value' => (string) array_sum(array_column($lpjStatuses, 'count')),
                    'note' => 'Running, review, dan completed',
                ],
                [
                    'label' => 'Export Queue',
                    'value' => (string) DB::table('document_exports')
                        ->where('organization_id', $context->organizationId)
                        ->whereIn('status', ['queued', 'processing'])
                        ->count(),
                    'note' => 'PDF/DOCX menunggu job',
                ],
            ],
            'proposalStatuses' => $proposalStatuses,
            'lpjStatuses' => $lpjStatuses,
            'exportQueue' => $exportQueue,
            'recentProjects' => $this->recentProjects($context->organizationId),
        ];
    }

    /**
     * @return array<int, array{id: int, name: string, status: string, proposalStatus: string|null, lpjRequired: int, lpjComplete: int}>
     */
    private function recentProjects(int $organizationId): array
    {
        return DB::table('projects')
            ->leftJoin('proposal_drafts', 'proposal_drafts.project_id', '=', 'projects.id')
            ->leftJoin('lpj_checklist_items', 'lpj_checklist_items.project_id', '=', 'projects.id')
            ->where('projects.organization_id', $organizationId)
            ->groupBy('projects.id', 'projects.name', 'projects.status', 'proposal_drafts.status', 'projects.updated_at')
            ->orderByDesc('projects.updated_at')
            ->limit(5)
            ->get([
                'projects.id',
                'projects.name',
                'projects.status',
                'proposal_drafts.status as proposal_status',
                DB::raw('sum(case when lpj_checklist_items.is_required = 1 then 1 else 0 end) as lpj_required'),
                DB::raw('sum(case when lpj_checklist_items.is_complete = 1 then 1 else 0 end) as lpj_complete'),
            ])
            ->map(static fn (object $project): array => [
                'id' => (int) $project->id,
                'name' => (string) $project->name,
                'status' => (string) $project->status,
                'proposalStatus' => $project->proposal_status === null ? null : (string) $project->proposal_status,
                'lpjRequired' => (int) $project->lpj_required,
                'lpjComplete' => (int) $project->lpj_complete,
            ])
            ->all();
    }
}
