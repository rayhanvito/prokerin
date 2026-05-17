<?php

declare(strict_types=1);

namespace App\Actions\Letter;

use App\Actions\Workspace\GetActiveOrganizationContextAction;
use App\Domain\Letter\LetterType;
use Illuminate\Support\Facades\DB;

final class GetLetterTemplatePayloadAction
{
    public function __construct(private readonly GetActiveOrganizationContextAction $activeOrganizationContext) {}

    /**
     * @return array{templates: array<int, array{id: int, name: string, letterType: string, letterTypeLabel: string, numberingPattern: string, signatoryUserId: int|null, signatoryName: string|null, isActive: bool, templateHtml: string}>, members: array<int, array{id: int, name: string, role: string}>, types: array<int, array{value: string, label: string}>}
     */
    public function execute(int $actorUserId, ?int $preferredOrganizationId = null): array
    {
        $context = $this->activeOrganizationContext->execute($actorUserId, $preferredOrganizationId);

        return [
            'templates' => DB::table('letter_templates')
                ->leftJoin('users', 'users.id', '=', 'letter_templates.signatory_user_id')
                ->where('letter_templates.organization_id', $context->organizationId)
                ->orderBy('letter_templates.letter_type')
                ->orderBy('letter_templates.name')
                ->get([
                    'letter_templates.id',
                    'letter_templates.name',
                    'letter_templates.letter_type',
                    'letter_templates.numbering_pattern',
                    'letter_templates.signatory_user_id',
                    'letter_templates.is_active',
                    'letter_templates.template_html',
                    'users.name as signatory_name',
                ])
                ->map(static fn (object $template): array => [
                    'id' => (int) $template->id,
                    'name' => (string) $template->name,
                    'letterType' => (string) $template->letter_type,
                    'letterTypeLabel' => LetterType::from((string) $template->letter_type)->label(),
                    'numberingPattern' => (string) $template->numbering_pattern,
                    'signatoryUserId' => $template->signatory_user_id === null ? null : (int) $template->signatory_user_id,
                    'signatoryName' => is_string($template->signatory_name) ? $template->signatory_name : null,
                    'isActive' => (bool) $template->is_active,
                    'templateHtml' => (string) $template->template_html,
                ])
                ->all(),
            'members' => $this->members($context->organizationId),
            'types' => array_map(static fn (LetterType $type): array => ['value' => $type->value, 'label' => $type->label()], LetterType::cases()),
        ];
    }

    private function members(int $organizationId): array
    {
        return DB::table('organization_members')
            ->join('users', 'users.id', '=', 'organization_members.user_id')
            ->where('organization_members.organization_id', $organizationId)
            ->orderBy('users.name')
            ->get(['users.id', 'users.name', 'organization_members.role'])
            ->map(static fn (object $member): array => [
                'id' => (int) $member->id,
                'name' => (string) $member->name,
                'role' => (string) $member->role,
            ])
            ->all();
    }
}
