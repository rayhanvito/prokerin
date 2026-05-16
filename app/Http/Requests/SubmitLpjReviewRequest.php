<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Domain\Organization\OrganizationRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

final class SubmitLpjReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $projectId = $this->route('project');

        if ($user === null || ! is_numeric($projectId)) {
            return false;
        }

        return DB::table('projects')
            ->join('organization_members', 'organization_members.organization_id', '=', 'projects.organization_id')
            ->where('projects.id', (int) $projectId)
            ->where('organization_members.user_id', $user->id)
            ->whereIn('organization_members.role', [
                OrganizationRole::Owner->value,
                OrganizationRole::Admin->value,
                OrganizationRole::Secretary->value,
            ])
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
