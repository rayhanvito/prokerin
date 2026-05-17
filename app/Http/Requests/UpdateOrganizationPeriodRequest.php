<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Support\Roles;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

final class UpdateOrganizationPeriodRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        if ($user === null) {
            return false;
        }

        $period = DB::table('organization_periods')
            ->where('id', $this->route('period'))
            ->first(['organization_id']);

        if ($period === null) {
            return false;
        }

        return DB::table('organization_members')
            ->where('organization_id', (int) $period->organization_id)
            ->where('user_id', $user->id)
            ->whereIn('role', Roles::ORGANIZATION_MANAGERS)
            ->exists();
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after_or_equal:starts_at'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
