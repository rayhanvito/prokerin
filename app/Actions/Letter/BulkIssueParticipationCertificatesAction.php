<?php

declare(strict_types=1);

namespace App\Actions\Letter;

use App\Actions\Letter\Concerns\AuthorizesLetterAccess;
use App\Domain\Letter\LetterType;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class BulkIssueParticipationCertificatesAction
{
    use AuthorizesLetterAccess;

    public function __construct(private readonly DraftLetterAction $draftLetter) {}

    /**
     * @param  array<int, int>  $recipientUserIds
     * @return array<int, int>
     *
     * @throws AuthorizationException
     */
    public function execute(int $actorUserId, int $projectId, array $recipientUserIds): array
    {
        $project = DB::table('projects')->where('id', $projectId)->first(['id', 'organization_id', 'name']);

        if ($project === null) {
            throw new NotFoundHttpException('Proker tidak ditemukan.');
        }

        $this->authorizeActiveOrganizationRole($actorUserId, (int) $project->organization_id, ['organization_owner', 'organization_admin', 'secretary']);

        $templateId = (int) DB::table('letter_templates')
            ->where('organization_id', $project->organization_id)
            ->where('letter_type', LetterType::ParticipationCertificate->value)
            ->where('is_active', true)
            ->orderBy('id')
            ->value('id');

        if ($templateId === 0) {
            throw new NotFoundHttpException('Template surat keterangan partisipasi belum tersedia.');
        }

        $members = DB::table('organization_members')
            ->join('users', 'users.id', '=', 'organization_members.user_id')
            ->where('organization_members.organization_id', $project->organization_id)
            ->whereIn('users.id', $recipientUserIds)
            ->get(['users.id', 'users.name']);

        $letterIds = [];

        foreach ($members as $member) {
            $letterIds[] = $this->draftLetter->execute(
                actorUserId: $actorUserId,
                templateId: $templateId,
                projectId: $projectId,
                subject: 'Surat Keterangan Partisipasi '.$project->name,
                recipientName: (string) $member->name,
                recipientOrganization: null,
                bodyData: ['contact_person' => (string) $member->name],
            );
        }

        return $letterIds;
    }
}
