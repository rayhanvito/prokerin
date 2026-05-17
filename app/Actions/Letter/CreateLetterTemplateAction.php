<?php

declare(strict_types=1);

namespace App\Actions\Letter;

use App\Actions\Letter\Concerns\AuthorizesLetterAccess;
use App\Domain\Letter\LetterType;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

final class CreateLetterTemplateAction
{
    use AuthorizesLetterAccess;

    /**
     * @throws AuthorizationException
     */
    public function execute(
        int $actorUserId,
        int $organizationId,
        string $name,
        LetterType $letterType,
        string $templateHtml,
        string $numberingPattern,
        ?int $signatoryUserId,
    ): int {
        $this->authorizeActiveOrganizationRole($actorUserId, $organizationId, ['organization_owner', 'organization_admin']);
        $this->ensureAllowedPlaceholders($templateHtml);
        $this->ensureSignatoryBelongsToOrganization($organizationId, $signatoryUserId);

        return (int) DB::table('letter_templates')->insertGetId([
            'organization_id' => $organizationId,
            'name' => $name,
            'letter_type' => $letterType->value,
            'template_html' => $templateHtml,
            'numbering_pattern' => $numberingPattern,
            'signatory_user_id' => $signatoryUserId,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function ensureAllowedPlaceholders(string $templateHtml): void
    {
        preg_match_all('/{{\s*([a-zA-Z0-9_]+)\s*}}/', $templateHtml, $matches);

        $allowed = [
            'org_name',
            'org_address',
            'letter_number',
            'letter_date',
            'letter_subject',
            'recipient_name',
            'recipient_organization',
            'project_name',
            'event_date',
            'event_location',
            'contact_person',
            'signatory_name',
            'signatory_role',
        ];

        foreach ($matches[1] ?? [] as $placeholder) {
            if (! in_array($placeholder, $allowed, true)) {
                throw new InvalidArgumentException("Placeholder {$placeholder} tidak didukung.");
            }
        }
    }

    private function ensureSignatoryBelongsToOrganization(int $organizationId, ?int $signatoryUserId): void
    {
        if ($signatoryUserId === null) {
            return;
        }

        $exists = DB::table('organization_members')
            ->where('organization_id', $organizationId)
            ->where('user_id', $signatoryUserId)
            ->exists();

        if (! $exists) {
            throw new InvalidArgumentException('Penandatangan harus anggota organisasi.');
        }
    }
}
