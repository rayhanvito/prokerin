<?php

declare(strict_types=1);

namespace App\Actions\Letter;

use App\Actions\Workspace\GetActiveOrganizationContextAction;
use Illuminate\Support\Facades\DB;

final class GetLetterCreatePayloadAction
{
    public function __construct(private readonly GetActiveOrganizationContextAction $activeOrganizationContext) {}

    /**
     * @return array{templates: array<int, array{id: int, name: string, letterType: string, signatoryName: string|null}>, projects: array<int, array{id: int, name: string, startsAt: string|null, endsAt: string|null}>, placeholders: array<int, string>}
     */
    public function execute(int $actorUserId, ?int $preferredOrganizationId = null): array
    {
        $context = $this->activeOrganizationContext->execute($actorUserId, $preferredOrganizationId);

        return [
            'templates' => DB::table('letter_templates')
                ->leftJoin('users', 'users.id', '=', 'letter_templates.signatory_user_id')
                ->where('letter_templates.organization_id', $context->organizationId)
                ->where('letter_templates.is_active', true)
                ->orderBy('letter_templates.name')
                ->get(['letter_templates.id', 'letter_templates.name', 'letter_templates.letter_type', 'users.name as signatory_name'])
                ->map(static fn (object $template): array => [
                    'id' => (int) $template->id,
                    'name' => (string) $template->name,
                    'letterType' => (string) $template->letter_type,
                    'signatoryName' => is_string($template->signatory_name) ? $template->signatory_name : null,
                ])
                ->all(),
            'projects' => DB::table('projects')
                ->where('organization_id', $context->organizationId)
                ->orderByDesc('created_at')
                ->get(['id', 'name', 'starts_at', 'ends_at'])
                ->map(static fn (object $project): array => [
                    'id' => (int) $project->id,
                    'name' => (string) $project->name,
                    'startsAt' => is_string($project->starts_at) ? $project->starts_at : null,
                    'endsAt' => is_string($project->ends_at) ? $project->ends_at : null,
                ])
                ->all(),
            'placeholders' => [
                'org_name',
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
            ],
        ];
    }
}
