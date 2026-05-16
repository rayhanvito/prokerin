<?php

declare(strict_types=1);

namespace App\Actions\Dashboard\Variants;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

final class SekretarisDashboardPayloadAction
{
    /**
     * @return array<string, mixed>
     */
    public function execute(int $actorUserId, int $organizationId): array
    {
        return Cache::remember("dashboard:sekretaris:{$organizationId}:{$actorUserId}", 300, fn (): array => [
            'kpiMetrics' => [
                ['label' => 'Proker Aktif', 'value' => DB::table('projects')->where('organization_id', $organizationId)->whereNotIn('status', ['completed', 'archived'])->count()],
                ['label' => 'Proposal Pending', 'value' => DB::table('proposal_drafts')->join('projects', 'projects.id', '=', 'proposal_drafts.project_id')->where('projects.organization_id', $organizationId)->where('proposal_drafts.status', 'submitted')->count()],
                ['label' => 'Rapat Minggu Ini', 'value' => DB::table('meetings')->where('organization_id', $organizationId)->whereBetween('starts_at', [now(), now()->addWeek()])->count()],
                ['label' => 'Dokumen Belum Lengkap', 'value' => DB::table('documents')->where('organization_id', $organizationId)->where('status', 'review')->count()],
            ],
            'proposalStatusOverview' => $this->proposalStatusOverview($organizationId),
            'lpjChecklistOverview' => $this->lpjChecklistOverview($organizationId),
            'meetingsWithoutMinutes' => $this->meetingsWithoutMinutes($organizationId),
            'pendingInvitations' => $this->pendingInvitations($organizationId),
            'recentDocuments' => $this->recentDocuments($organizationId),
        ]);
    }

    private function proposalStatusOverview(int $organizationId): array
    {
        return DB::table('projects')
            ->leftJoin('proposal_drafts', 'proposal_drafts.project_id', '=', 'projects.id')
            ->where('projects.organization_id', $organizationId)
            ->orderBy('projects.name')
            ->limit(8)
            ->get(['projects.name', 'projects.slug', 'proposal_drafts.status'])
            ->map(static fn (object $row): array => [
                'projectName' => (string) $row->name,
                'slug' => (string) $row->slug,
                'status' => (string) ($row->status ?? 'draft'),
            ])
            ->all();
    }

    private function lpjChecklistOverview(int $organizationId): array
    {
        return DB::table('projects')
            ->leftJoin('lpj_checklist_items', 'lpj_checklist_items.project_id', '=', 'projects.id')
            ->where('projects.organization_id', $organizationId)
            ->groupBy('projects.id', 'projects.name')
            ->orderBy('projects.name')
            ->limit(8)
            ->get([
                'projects.name',
                DB::raw('count(lpj_checklist_items.id) as total_items'),
                DB::raw('sum(case when lpj_checklist_items.is_complete = 1 then 1 else 0 end) as completed_items'),
            ])
            ->map(static function (object $row): array {
                $total = (int) $row->total_items;
                $complete = (int) $row->completed_items;

                return [
                    'projectName' => (string) $row->name,
                    'completed' => $complete,
                    'total' => $total,
                    'progressPercentage' => $total === 0 ? 0 : (int) round(($complete / $total) * 100),
                ];
            })
            ->all();
    }

    private function meetingsWithoutMinutes(int $organizationId): array
    {
        return DB::table('meetings')
            ->leftJoin('meeting_minutes', 'meeting_minutes.meeting_id', '=', 'meetings.id')
            ->where('meetings.organization_id', $organizationId)
            ->whereNull('meeting_minutes.id')
            ->orderByDesc('meetings.starts_at')
            ->limit(5)
            ->get(['meetings.id', 'meetings.title', 'meetings.starts_at'])
            ->map(static fn (object $meeting): array => [
                'id' => (int) $meeting->id,
                'title' => (string) $meeting->title,
                'startsAt' => (string) $meeting->starts_at,
            ])
            ->all();
    }

    private function pendingInvitations(int $organizationId): array
    {
        return DB::table('organization_invitations')
            ->where('organization_id', $organizationId)
            ->where('status', 'pending')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get(['id', 'email', 'role', 'expires_at'])
            ->map(static fn (object $invite): array => [
                'id' => (int) $invite->id,
                'email' => (string) $invite->email,
                'role' => (string) $invite->role,
                'expiresAt' => $invite->expires_at === null ? null : (string) $invite->expires_at,
            ])
            ->all();
    }

    private function recentDocuments(int $organizationId): array
    {
        return DB::table('documents')
            ->leftJoin('projects', 'projects.id', '=', 'documents.project_id')
            ->where('documents.organization_id', $organizationId)
            ->orderByDesc('documents.created_at')
            ->limit(5)
            ->get(['documents.id', 'documents.name', 'documents.folder', 'documents.status', 'documents.created_at', 'projects.name as project_name'])
            ->map(static fn (object $document): array => [
                'id' => (int) $document->id,
                'name' => (string) $document->name,
                'folder' => (string) $document->folder,
                'projectName' => (string) ($document->project_name ?? '-'),
                'status' => (string) $document->status,
                'uploadedAt' => (string) $document->created_at,
            ])
            ->all();
    }
}
