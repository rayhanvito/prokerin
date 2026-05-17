<?php

declare(strict_types=1);

namespace App\Actions\Letter;

use App\Actions\Workspace\GetActiveOrganizationContextAction;
use App\Domain\Letter\LetterStatus;
use App\Domain\Letter\LetterType;
use Illuminate\Support\Facades\DB;

final class GetLetterIndexPayloadAction
{
    public function __construct(private readonly GetActiveOrganizationContextAction $activeOrganizationContext) {}

    /**
     * @return array{metrics: array{total: int, draft: int, submitted: int, signed: int}, letters: array<int, array{id: int, letterNumber: string, subject: string, type: string, typeLabel: string, recipientName: string, status: string, statusLabel: string, projectName: string|null, createdAt: string}>, filters: array{types: array<int, array{value: string, label: string}>, statuses: array<int, array{value: string, label: string}>}}
     */
    public function execute(int $actorUserId, ?int $preferredOrganizationId = null): array
    {
        $context = $this->activeOrganizationContext->execute($actorUserId, $preferredOrganizationId);
        $letters = DB::table('letters')
            ->leftJoin('projects', 'projects.id', '=', 'letters.project_id')
            ->where('letters.organization_id', $context->organizationId)
            ->orderByDesc('letters.created_at')
            ->limit(60)
            ->get([
                'letters.id',
                'letters.letter_number',
                'letters.letter_type',
                'letters.subject',
                'letters.recipient_name',
                'letters.status',
                'letters.created_at',
                'projects.name as project_name',
            ]);

        return [
            'metrics' => [
                'total' => DB::table('letters')->where('organization_id', $context->organizationId)->count(),
                'draft' => DB::table('letters')->where('organization_id', $context->organizationId)->where('status', LetterStatus::Draft->value)->count(),
                'submitted' => DB::table('letters')->where('organization_id', $context->organizationId)->where('status', LetterStatus::Submitted->value)->count(),
                'signed' => DB::table('letters')->where('organization_id', $context->organizationId)->whereIn('status', [LetterStatus::Signed->value, LetterStatus::Sent->value])->count(),
            ],
            'letters' => $letters
                ->map(static fn (object $letter): array => [
                    'id' => (int) $letter->id,
                    'letterNumber' => (string) $letter->letter_number,
                    'subject' => (string) $letter->subject,
                    'type' => (string) $letter->letter_type,
                    'typeLabel' => LetterType::from((string) $letter->letter_type)->label(),
                    'recipientName' => (string) $letter->recipient_name,
                    'status' => (string) $letter->status,
                    'statusLabel' => LetterStatus::from((string) $letter->status)->label(),
                    'projectName' => is_string($letter->project_name) ? $letter->project_name : null,
                    'createdAt' => (string) $letter->created_at,
                ])
                ->all(),
            'filters' => [
                'types' => array_map(static fn (LetterType $type): array => ['value' => $type->value, 'label' => $type->label()], LetterType::cases()),
                'statuses' => array_map(static fn (LetterStatus $status): array => ['value' => $status->value, 'label' => $status->label()], LetterStatus::cases()),
            ],
        ];
    }
}
