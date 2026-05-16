<?php

declare(strict_types=1);

namespace App\Actions\Proposal;

use App\Domain\Project\ProjectRole;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class UpdateProposalDraftSectionsAction
{
    /**
     * @param  array<int, array{heading: string, body: string}>  $sections
     *
     * @throws ValidationException
     */
    public function execute(int $actorUserId, int $proposalDraftId, array $sections): void
    {
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
                'proposal_drafts.status',
                'projects.status as project_status',
            ])
            ->first();

        if ($draft === null) {
            throw new NotFoundHttpException('Proposal draft was not found for the active workspace.');
        }

        if (! $this->isEditable((string) $draft->status, (string) $draft->project_status)) {
            throw ValidationException::withMessages([
                'sections' => 'Only draft proposals or revision requests can be edited.',
            ]);
        }

        DB::table('proposal_drafts')
            ->where('id', $proposalDraftId)
            ->update([
                'sections' => json_encode($sections),
                'status' => 'draft',
                'updated_at' => now(),
            ]);
    }

    private function isEditable(string $draftStatus, string $projectStatus): bool
    {
        return $draftStatus === 'draft' && in_array($projectStatus, ['draft', 'proposal_review'], true)
            || $draftStatus === 'revision_requested' && $projectStatus === 'draft';
    }
}
