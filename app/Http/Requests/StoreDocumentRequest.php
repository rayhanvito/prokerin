<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Domain\Document\DocumentVisibility;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreDocumentRequest extends FormRequest
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
            'file' => [
                'required',
                'file',
                'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,zip',
                'max:10240',
            ],
            'folder' => ['required', 'string', 'min:2', 'max:120'],
            'visibility' => ['required', Rule::enum(DocumentVisibility::class)],
            'project_id' => ['nullable', 'integer'],
        ];
    }
}
