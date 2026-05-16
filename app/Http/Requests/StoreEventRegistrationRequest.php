<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreEventRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'participant_name' => ['required', 'string', 'max:160'],
            'participant_email' => ['required', 'email', 'max:160'],
            'phone' => ['nullable', 'string', 'max:40'],
            'institution' => ['nullable', 'string', 'max:160'],
            'ticket_tier_id' => ['nullable', 'integer'],
        ];
    }
}
