<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Domain\Organization\OrganizationRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

final class UpdateOrganizationMemberRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $membershipId = $this->route('member');

        if ($user === null || ! is_numeric($membershipId)) {
            return false;
        }

        $targetMembership = DB::table('organization_members')
            ->where('id', (int) $membershipId)
            ->first();

        if ($targetMembership === null) {
            return false;
        }

        return DB::table('organization_members')
            ->where('organization_id', $targetMembership->organization_id)
            ->where('user_id', $user->id)
            ->whereIn('role', [OrganizationRole::Owner->value, OrganizationRole::Admin->value])
            ->exists();
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'role' => ['required', Rule::enum(OrganizationRole::class)],
        ];
    }
}
