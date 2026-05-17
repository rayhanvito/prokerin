<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Http\Requests\Concerns\AuthorizesProjectMicrositeManagement;
use Illuminate\Foundation\Http\FormRequest;

final class UpdateMicrositeSettingsRequest extends FormRequest
{
    use AuthorizesProjectMicrositeManagement;

    public function authorize(): bool
    {
        return $this->canManageProjectMicrosite();
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'description_md' => ['nullable', 'string', 'max:12000'],
            'location_text' => ['nullable', 'string', 'max:180'],
            'location_maps_url' => ['nullable', 'url', 'max:500'],
            'contact_name' => ['nullable', 'string', 'max:120'],
            'contact_whatsapp' => ['nullable', 'string', 'max:40'],
            'contact_email' => ['nullable', 'email', 'max:160'],
            'show_countdown' => ['required', 'boolean'],
            'show_committee' => ['required', 'boolean'],
            'show_gallery' => ['required', 'boolean'],
            'meta_title' => ['nullable', 'string', 'max:70'],
            'meta_description' => ['nullable', 'string', 'max:180'],
        ];
    }
}
