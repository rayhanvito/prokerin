<?php

declare(strict_types=1);

namespace App\Actions\Letter;

use App\Actions\Letter\Concerns\AuthorizesLetterAccess;
use App\Domain\Letter\LetterStatus;
use App\Domain\Letter\LetterType;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class DraftLetterAction
{
    use AuthorizesLetterAccess;

    public function __construct(private readonly GenerateLetterNumberAction $generateLetterNumber) {}

    /**
     * @param  array<string, scalar|null>  $bodyData
     *
     * @throws AuthorizationException
     */
    public function execute(
        int $actorUserId,
        int $templateId,
        ?int $projectId,
        string $subject,
        string $recipientName,
        ?string $recipientOrganization,
        array $bodyData,
    ): int {
        $template = DB::table('letter_templates')
            ->where('id', $templateId)
            ->where('is_active', true)
            ->first(['id', 'organization_id', 'letter_type', 'numbering_pattern', 'signatory_user_id']);

        if ($template === null) {
            throw new NotFoundHttpException('Template surat tidak ditemukan.');
        }

        $this->authorizeActiveOrganizationRole($actorUserId, (int) $template->organization_id, ['organization_owner', 'organization_admin', 'secretary']);
        $project = $this->project($projectId, (int) $template->organization_id);
        $organization = DB::table('organizations')->where('id', $template->organization_id)->first(['name']);
        $signatory = $template->signatory_user_id === null
            ? null
            : DB::table('users')->where('id', $template->signatory_user_id)->first(['name']);
        $now = now();
        $letterType = LetterType::from((string) $template->letter_type);
        $letterNumber = $this->generateLetterNumber->execute(
            organizationId: (int) $template->organization_id,
            letterType: $letterType,
            year: (int) $now->format('Y'),
            month: (int) $now->format('n'),
            numberingPattern: (string) $template->numbering_pattern,
        );

        $mergedBodyData = array_merge([
            'org_name' => (string) ($organization->name ?? ''),
            'org_address' => '',
            'letter_number' => $letterNumber,
            'letter_date' => $now->translatedFormat('d F Y'),
            'letter_subject' => $subject,
            'recipient_name' => $recipientName,
            'recipient_organization' => $recipientOrganization ?? '',
            'project_name' => $project?->name ?? '',
            'event_date' => $this->projectDate($project),
            'event_location' => $bodyData['event_location'] ?? '',
            'contact_person' => $bodyData['contact_person'] ?? '',
            'signatory_name' => (string) ($signatory->name ?? ''),
            'signatory_role' => 'Ketua Organisasi',
        ], $bodyData);

        return (int) DB::table('letters')->insertGetId([
            'organization_id' => (int) $template->organization_id,
            'template_id' => $templateId,
            'project_id' => $projectId,
            'letter_number' => $letterNumber,
            'letter_type' => $letterType->value,
            'subject' => $subject,
            'body_data' => json_encode($mergedBodyData, JSON_THROW_ON_ERROR),
            'recipient_name' => $recipientName,
            'recipient_organization' => $recipientOrganization,
            'status' => LetterStatus::Draft->value,
            'drafted_by_user_id' => $actorUserId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    private function project(?int $projectId, int $organizationId): ?object
    {
        if ($projectId === null) {
            return null;
        }

        $project = DB::table('projects')
            ->where('id', $projectId)
            ->where('organization_id', $organizationId)
            ->first(['id', 'name', 'starts_at', 'ends_at']);

        if ($project === null) {
            throw new NotFoundHttpException('Proker tidak ditemukan untuk organisasi ini.');
        }

        return $project;
    }

    private function projectDate(?object $project): string
    {
        if ($project === null || $project->starts_at === null) {
            return '';
        }

        $startsAt = Carbon::parse((string) $project->starts_at)->translatedFormat('d F Y');

        if ($project->ends_at === null || $project->ends_at === $project->starts_at) {
            return $startsAt;
        }

        return $startsAt.' - '.Carbon::parse((string) $project->ends_at)->translatedFormat('d F Y');
    }
}
