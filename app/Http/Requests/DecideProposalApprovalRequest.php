<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Domain\Organization\OrganizationRole;
use App\Domain\Proposal\ProposalApprovalDecision;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

final class DecideProposalApprovalRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $proposalDraftId = $this->route('proposalDraft');

        if ($user === null || ! is_numeric($proposalDraftId)) {
            return false;
        }

        return DB::table('proposal_drafts')
            ->join('projects', 'projects.id', '=', 'proposal_drafts.project_id')
            ->join('organization_members', 'organization_members.organization_id', '=', 'projects.organization_id')
            ->where('proposal_drafts.id', (int) $proposalDraftId)
            ->where('organization_members.user_id', $user->id)
            ->whereIn('organization_members.role', [OrganizationRole::Owner->value, OrganizationRole::Admin->value])
            ->exists();
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'decision' => ['required', Rule::enum(ProposalApprovalDecision::class)],
        ];
    }
}
