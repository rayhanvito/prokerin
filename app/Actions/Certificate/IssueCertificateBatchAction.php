<?php

declare(strict_types=1);

namespace App\Actions\Certificate;

use App\Jobs\GenerateCertificatePdfJob;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class IssueCertificateBatchAction
{
    public function __construct(
        private CertificateNumberGenerator $certificateNumberGenerator,
    ) {}

    /**
     * @param  array<int, array{user_id?: int|null, recipient_name: string, recipient_email?: string|null}>  $recipients
     * @return array{issued: int, certificateIds: array<int, int>}
     */
    public function execute(
        int $actorUserId,
        int $templateId,
        array $recipients,
        ?int $projectId = null,
        ?int $meetingId = null,
        ?Carbon $issuedAt = null,
    ): array {
        $issuedAt ??= now();
        $template = $this->authorizedTemplate($actorUserId, $templateId);

        abort_if($template === null, 403);

        if ($projectId !== null) {
            $projectBelongsToOrganization = DB::table('projects')
                ->where('id', $projectId)
                ->where('organization_id', $template->organization_id)
                ->exists();

            abort_unless($projectBelongsToOrganization, 422);
        }

        if ($meetingId !== null) {
            $meetingBelongsToOrganization = DB::table('meetings')
                ->where('id', $meetingId)
                ->where('organization_id', $template->organization_id)
                ->exists();

            abort_unless($meetingBelongsToOrganization, 422);
        }

        $certificateIds = DB::transaction(function () use ($actorUserId, $issuedAt, $meetingId, $projectId, $recipients, $template): array {
            $ids = [];

            foreach ($recipients as $recipient) {
                $userId = $recipient['user_id'] ?? null;

                if ($userId !== null) {
                    $isMember = DB::table('organization_members')
                        ->where('organization_id', $template->organization_id)
                        ->where('user_id', $userId)
                        ->exists();

                    abort_unless($isMember, 422);
                }

                $ids[] = (int) DB::table('certificate_recipients')->insertGetId([
                    'organization_id' => $template->organization_id,
                    'template_id' => $template->id,
                    'user_id' => $userId,
                    'recipient_name' => $recipient['recipient_name'],
                    'recipient_email' => $recipient['recipient_email'] ?? null,
                    'project_id' => $projectId,
                    'meeting_id' => $meetingId,
                    'certificate_number' => $this->certificateNumberGenerator->generate((int) $template->organization_id, (int) $issuedAt->format('Y')),
                    'issued_at' => $issuedAt,
                    'issued_by' => $actorUserId,
                    'verification_token' => (string) Str::uuid(),
                    'created_at' => $issuedAt,
                    'updated_at' => $issuedAt,
                ]);
            }

            return $ids;
        });

        foreach ($certificateIds as $certificateId) {
            GenerateCertificatePdfJob::dispatch($certificateId)->onQueue('exports');
        }

        return [
            'issued' => count($certificateIds),
            'certificateIds' => $certificateIds,
        ];
    }

    private function authorizedTemplate(int $actorUserId, int $templateId): ?object
    {
        return DB::table('certificate_templates')
            ->join('organization_members', 'organization_members.organization_id', '=', 'certificate_templates.organization_id')
            ->where('certificate_templates.id', $templateId)
            ->where('organization_members.user_id', $actorUserId)
            ->whereIn('organization_members.role', ['organization_owner', 'organization_admin'])
            ->select('certificate_templates.*')
            ->first();
    }
}
