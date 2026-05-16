<?php

declare(strict_types=1);

namespace App\Actions\Workspace;

use Illuminate\Support\Facades\DB;

final class GetProposalDraftPayloadAction
{
    /**
     * @return array{id: int|null, title: string, subtitle: string, sections: array<int, array{heading: string, body: string}>, status: string, projectSlug: string|null, projectStatus: string|null, canSubmit: bool}
     */
    public function execute(int $actorUserId): array
    {
        $draft = DB::table('proposal_drafts')
            ->join('projects', 'projects.id', '=', 'proposal_drafts.project_id')
            ->join('organization_members', 'organization_members.organization_id', '=', 'projects.organization_id')
            ->where('organization_members.user_id', $actorUserId)
            ->select([
                'proposal_drafts.id',
                'proposal_drafts.title',
                'proposal_drafts.subtitle',
                'proposal_drafts.sections',
                'proposal_drafts.status',
                'projects.slug as project_slug',
                'projects.status as project_status',
            ])
            ->orderBy('proposal_drafts.id')
            ->first();

        if ($draft === null) {
            return [
                'id' => null,
                'title' => 'Proposal',
                'subtitle' => 'Belum ada draft',
                'sections' => [],
                'status' => 'empty',
                'projectSlug' => null,
                'projectStatus' => null,
                'canSubmit' => false,
            ];
        }

        $status = (string) $draft->status;
        $projectStatus = (string) $draft->project_status;

        return [
            'id' => (int) $draft->id,
            'title' => (string) $draft->title,
            'subtitle' => (string) $draft->subtitle,
            'sections' => json_decode((string) $draft->sections, true) ?: [],
            'status' => $status,
            'projectSlug' => (string) $draft->project_slug,
            'projectStatus' => $projectStatus,
            'canSubmit' => $status === 'draft' && in_array($projectStatus, ['draft', 'proposal_review'], true),
        ];
    }
}
