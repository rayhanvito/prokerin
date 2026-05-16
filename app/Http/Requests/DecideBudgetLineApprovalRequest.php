<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

final class DecideBudgetLineApprovalRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $budgetLineId = $this->route('budgetLine');

        if ($user === null || ! is_numeric($budgetLineId)) {
            return false;
        }

        return DB::table('budget_lines')
            ->join('projects', 'projects.id', '=', 'budget_lines.project_id')
            ->join('organization_members', 'organization_members.organization_id', '=', 'projects.organization_id')
            ->where('budget_lines.id', (int) $budgetLineId)
            ->where('organization_members.user_id', $user->id)
            ->whereIn('organization_members.role', ['organization_owner', 'organization_admin', 'treasurer'])
            ->exists();
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'decision' => ['required', Rule::in(['approve', 'reject'])],
        ];
    }
}
