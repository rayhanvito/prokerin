<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Support\Roles;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

final class SendOrganizationInvitationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        if ($user === null) {
            return false;
        }

        $activeOrganizationId = $this->session()->get('active_organization_id');

        return DB::table('organization_members')
            ->where('user_id', $user->id)
            ->when(is_numeric($activeOrganizationId), static function ($query) use ($activeOrganizationId): void {
                $query->where('organization_id', (int) $activeOrganizationId);
            })
            ->whereIn('role', Roles::ORGANIZATION_MANAGERS)
            ->exists();
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email', 'max:160'],
            'role' => ['required', 'string', Rule::in(Roles::INVITABLE_ORGANIZATION_ROLES)],
        ];
    }
}
