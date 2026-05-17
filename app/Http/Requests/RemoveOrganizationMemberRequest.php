<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Support\Roles;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

final class RemoveOrganizationMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $memberId = $this->route('member');

        if ($user === null || ! is_numeric($memberId)) {
            return false;
        }

        $targetOrganizationId = DB::table('organization_members')
            ->where('id', (int) $memberId)
            ->value('organization_id');

        if ($targetOrganizationId === null) {
            return false;
        }

        return DB::table('organization_members')
            ->where('organization_id', (int) $targetOrganizationId)
            ->where('user_id', $user->id)
            ->where('role', Roles::ORGANIZATION_OWNER)
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
