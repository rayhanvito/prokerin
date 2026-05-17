<?php

declare(strict_types=1);

namespace App\Actions\Letter;

use App\Actions\Letter\Concerns\AuthorizesLetterAccess;
use App\Domain\Letter\LetterStatus;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class SignLetterAction
{
    use AuthorizesLetterAccess;

    public function __construct(private readonly GenerateLetterPdfAction $generateLetterPdf) {}

    /**
     * @throws AuthorizationException|ValidationException
     */
    public function execute(int $actorUserId, int $letterId): void
    {
        $letter = $this->letterForActor($actorUserId, $letterId);

        if ((int) ($letter->signatory_user_id ?? 0) !== $actorUserId) {
            throw new AuthorizationException('Hanya penandatangan template yang bisa menandatangani surat ini.');
        }

        if (! in_array((string) $letter->status, [LetterStatus::Draft->value, LetterStatus::Submitted->value], true)) {
            throw ValidationException::withMessages(['letter' => 'Surat ini tidak bisa ditandatangani.']);
        }

        $bodyData = json_decode((string) $letter->body_data, true, 512, JSON_THROW_ON_ERROR);
        $path = $this->generateLetterPdf->execute(
            organizationId: (int) $letter->organization_id,
            letterId: $letterId,
            templateHtml: (string) $letter->template_html,
            bodyData: is_array($bodyData) ? $bodyData : [],
        );

        DB::table('letters')
            ->where('id', $letterId)
            ->update([
                'rendered_pdf_path' => $path,
                'status' => LetterStatus::Signed->value,
                'signed_by_user_id' => $actorUserId,
                'signed_at' => now(),
                'updated_at' => now(),
            ]);
    }
}
