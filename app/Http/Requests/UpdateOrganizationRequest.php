<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Support\Roles;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

final class UpdateOrganizationRequest extends FormRequest
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
            ->where('role', Roles::ORGANIZATION_OWNER)
            ->exists();
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:120'],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
