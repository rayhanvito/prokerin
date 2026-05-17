<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Domain\Organization\Enums\PlanTier;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreOrganizationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:3', 'max:120'],
            'slug' => ['nullable', 'string', 'min:3', 'max:120', 'regex:/^[a-z0-9-]+$/', 'unique:organizations,slug'],
            'plan_tier' => ['nullable', Rule::enum(PlanTier::class)],
        ];
    }
}
