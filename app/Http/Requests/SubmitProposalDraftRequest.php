<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Domain\Project\ProjectRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

final class SubmitProposalDraftRequest extends FormRequest
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
            ->leftJoin('project_members', function ($join) use ($user): void {
                $join->on('project_members.project_id', '=', 'projects.id')
                    ->where('project_members.user_id', $user->id)
                    ->where('project_members.role', ProjectRole::ProjectLead->value);
            })
            ->where('proposal_drafts.id', (int) $proposalDraftId)
            ->where('organization_members.user_id', $user->id)
            ->where(function ($query): void {
                $query
                    ->whereIn('organization_members.role', ['organization_owner', 'organization_admin', 'secretary'])
                    ->orWhereNotNull('project_members.id');
            })
            ->exists();
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [];
    }
}
