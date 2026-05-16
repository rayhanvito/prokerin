<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

final class PublishMeetingMinutesRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        if ($user === null) {
            return false;
        }

        return DB::table('organization_members')
            ->where('user_id', $user->id)
            ->whereIn('role', ['organization_owner', 'organization_admin', 'secretary'])
            ->exists();
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'summary' => ['required', 'string', 'max:5000'],
            'decisions' => ['nullable', 'array'],
            'decisions.*' => ['string', 'max:500'],
            'action_items' => ['nullable', 'array'],
            'action_items.*.task' => ['required', 'string', 'max:500'],
            'action_items.*.owner' => ['nullable', 'string', 'max:255'],
            'action_items.*.due' => ['nullable', 'string', 'max:255'],
            'action_items.*.status' => ['nullable', 'string', 'in:open,in_progress,done'],
            'publish' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function validated($key = null, $default = null): array
    {
        $data = parent::validated();

        $data['decisions'] = $data['decisions'] ?? [];
        $data['action_items'] = $data['action_items'] ?? [];
        $data['publish'] = (bool) ($data['publish'] ?? false);

        return $data;
    }
}
