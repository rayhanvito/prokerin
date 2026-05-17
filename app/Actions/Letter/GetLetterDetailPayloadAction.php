<?php

declare(strict_types=1);

namespace App\Actions\Letter;

use App\Actions\Letter\Concerns\AuthorizesLetterAccess;
use App\Domain\Letter\LetterStatus;
use App\Domain\Letter\LetterType;
use Illuminate\Support\Facades\Storage;

final class GetLetterDetailPayloadAction
{
    use AuthorizesLetterAccess;

    public function __construct(private readonly RenderLetterTemplateAction $renderLetterTemplate) {}

    /**
     * @return array{letter: array{id: int, letterNumber: string, subject: string, type: string, typeLabel: string, recipientName: string, recipientOrganization: string|null, status: string, statusLabel: string, canSign: bool, canDownload: bool, downloadUrl: string|null, signedAt: string|null}, previewHtml: string}
     */
    public function execute(int $actorUserId, int $letterId): array
    {
        $letter = $this->letterForActor($actorUserId, $letterId);
        $bodyData = json_decode((string) $letter->body_data, true, 512, JSON_THROW_ON_ERROR);

        return [
            'letter' => [
                'id' => (int) $letter->id,
                'letterNumber' => (string) $letter->letter_number,
                'subject' => (string) $letter->subject,
                'type' => (string) $letter->letter_type,
                'typeLabel' => LetterType::from((string) $letter->letter_type)->label(),
                'recipientName' => (string) $letter->recipient_name,
                'recipientOrganization' => is_string($letter->recipient_organization) ? $letter->recipient_organization : null,
                'status' => (string) $letter->status,
                'statusLabel' => LetterStatus::from((string) $letter->status)->label(),
                'canSign' => (int) ($letter->signatory_user_id ?? 0) === $actorUserId && in_array((string) $letter->status, [LetterStatus::Draft->value, LetterStatus::Submitted->value], true),
                'canDownload' => is_string($letter->rendered_pdf_path) && Storage::disk('public')->exists($letter->rendered_pdf_path),
                'downloadUrl' => is_string($letter->rendered_pdf_path) ? route('letters.download', ['letter' => (int) $letter->id]) : null,
                'signedAt' => is_string($letter->signed_at) ? $letter->signed_at : null,
            ],
            'previewHtml' => $this->renderLetterTemplate->execute((string) $letter->template_html, is_array($bodyData) ? $bodyData : []),
        ];
    }
}
