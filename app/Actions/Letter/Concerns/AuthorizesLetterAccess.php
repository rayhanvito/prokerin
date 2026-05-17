<?php

declare(strict_types=1);

namespace App\Actions\Letter\Concerns;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

trait AuthorizesLetterAccess
{
    /**
     * @return object{organization_id: int, role: string}
     *
     * @throws AuthorizationException
     */
    private function authorizeActiveOrganizationRole(int $actorUserId, int $organizationId, array $allowedRoles): object
    {
        $membership = DB::table('organization_members')
            ->where('organization_id', $organizationId)
            ->where('user_id', $actorUserId)
            ->first(['organization_id', 'role']);

        if ($membership === null) {
            throw new NotFoundHttpException('Organisasi tidak ditemukan untuk user ini.');
        }

        if (! in_array((string) $membership->role, $allowedRoles, true)) {
            throw new AuthorizationException('Anda tidak berhak mengelola surat organisasi ini.');
        }

        return $membership;
    }

    /**
     * @return object{id: int, organization_id: int, template_id: int, project_id: int|null, letter_number: string, letter_type: string, subject: string, body_data: string, recipient_name: string, recipient_organization: string|null, rendered_pdf_path: string|null, status: string, drafted_by_user_id: int, signed_by_user_id: int|null, signed_at: string|null, signatory_user_id: int|null, template_html: string, numbering_pattern: string}
     */
    private function letterForActor(int $actorUserId, int $letterId): object
    {
        $letter = DB::table('letters')
            ->join('letter_templates', 'letter_templates.id', '=', 'letters.template_id')
            ->join('organization_members', 'organization_members.organization_id', '=', 'letters.organization_id')
            ->where('letters.id', $letterId)
            ->where('organization_members.user_id', $actorUserId)
            ->first([
                'letters.id',
                'letters.organization_id',
                'letters.template_id',
                'letters.project_id',
                'letters.letter_number',
                'letters.letter_type',
                'letters.subject',
                'letters.body_data',
                'letters.recipient_name',
                'letters.recipient_organization',
                'letters.rendered_pdf_path',
                'letters.status',
                'letters.drafted_by_user_id',
                'letters.signed_by_user_id',
                'letters.signed_at',
                'letter_templates.signatory_user_id',
                'letter_templates.template_html',
                'letter_templates.numbering_pattern',
            ]);

        if ($letter === null) {
            throw new NotFoundHttpException('Surat tidak ditemukan.');
        }

        return $letter;
    }
}
