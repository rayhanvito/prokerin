<?php

declare(strict_types=1);

namespace App\Actions\Proposal;

use App\Actions\Project\TransitionProjectStatusAction;
use App\Domain\Organization\OrganizationRole;
use App\Domain\Project\ProjectStatus;
use App\Domain\Proposal\ProposalApprovalDecision;
use DomainException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final readonly class DecideProposalApprovalAction
{
    public function __construct(
        private TransitionProjectStatusAction $transitionProjectStatus,
    ) {}

    /**
     * @throws AuthorizationException
     * @throws ValidationException
     */
    public function execute(int $actorUserId, int $proposalDraftId, ProposalApprovalDecision $decision): void
    {
        DB::transaction(function () use ($actorUserId, $proposalDraftId, $decision): void {
            $draft = DB::table('proposal_drafts')
                ->join('projects', 'projects.id', '=', 'proposal_drafts.project_id')
                ->where('proposal_drafts.id', $proposalDraftId)
                ->select([
                    'proposal_drafts.id',
                    'proposal_drafts.project_id',
                    'proposal_drafts.status as draft_status',
                    'projects.organization_id',
                    'projects.status as project_status',
                ])
                ->lockForUpdate()
                ->first();

            if ($draft === null) {
                throw new NotFoundHttpException('Proposal draft was not found.');
            }

            $actorMembership = DB::table('organization_members')
                ->where('organization_id', (int) $draft->organization_id)
                ->where('user_id', $actorUserId)
                ->first();

            if ($actorMembership === null || ! $this->canDecide((string) $actorMembership->role)) {
                throw new AuthorizationException('You are not allowed to decide proposal approvals.');
            }

            if ((string) $draft->draft_status !== 'submitted') {
                throw ValidationException::withMessages([
                    'decision' => 'Only submitted proposals can receive an approval decision.',
                ]);
            }

            $targetProjectStatus = $decision === ProposalApprovalDecision::Approve
                ? ProjectStatus::RabApproval
                : ProjectStatus::Draft;

            try {
                $newProjectStatus = $this->transitionProjectStatus->execute(
                    ProjectStatus::from((string) $draft->project_status),
                    $targetProjectStatus,
                );
            } catch (DomainException) {
                throw ValidationException::withMessages([
                    'decision' => 'Proposal decision is not valid for the current proker status.',
                ]);
            }

            $now = now();

            DB::table('proposal_drafts')
                ->where('id', $proposalDraftId)
                ->update([
                    'status' => $decision === ProposalApprovalDecision::Approve ? 'approved' : 'revision_requested',
                    'updated_at' => $now,
                ]);

            DB::table('projects')
                ->where('id', (int) $draft->project_id)
                ->update([
                    'status' => $newProjectStatus->value,
                    'updated_at' => $now,
                ]);
        });
    }

    private function canDecide(string $role): bool
    {
        return in_array($role, [
            OrganizationRole::Owner->value,
            OrganizationRole::Admin->value,
        ], true);
    }
}
