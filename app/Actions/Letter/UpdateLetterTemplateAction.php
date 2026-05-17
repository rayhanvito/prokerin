<?php

declare(strict_types=1);

namespace App\Actions\Letter;

use App\Actions\Letter\Concerns\AuthorizesLetterAccess;
use App\Domain\Letter\LetterType;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class UpdateLetterTemplateAction
{
    use AuthorizesLetterAccess;

    /**
     * @throws AuthorizationException
     */
    public function execute(
        int $actorUserId,
        int $templateId,
        string $name,
        LetterType $letterType,
        string $templateHtml,
        string $numberingPattern,
        ?int $signatoryUserId,
        bool $isActive,
    ): void {
        $template = DB::table('letter_templates')->where('id', $templateId)->first(['organization_id']);

        if ($template === null) {
            throw new NotFoundHttpException('Template surat tidak ditemukan.');
        }

        $this->authorizeActiveOrganizationRole($actorUserId, (int) $template->organization_id, ['organization_owner', 'organization_admin']);

        DB::table('letter_templates')
            ->where('id', $templateId)
            ->update([
                'name' => $name,
                'letter_type' => $letterType->value,
                'template_html' => $templateHtml,
                'numbering_pattern' => $numberingPattern,
                'signatory_user_id' => $signatoryUserId,
                'is_active' => $isActive,
                'updated_at' => now(),
            ]);
    }
}
