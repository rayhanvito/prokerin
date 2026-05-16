<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Support\Roles;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

final class StoreBudgetLineRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        if ($user === null) {
            return false;
        }

        return DB::table('organization_members')
            ->where('user_id', $user->id)
            ->whereIn('role', Roles::FINANCE_MANAGERS)
            ->exists();
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'project_id' => ['required', 'integer', 'exists:projects,id'],
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:120'],
            'planned_amount' => ['required', 'integer', 'min:0', 'max:999999999999'],
        ];
    }
}
