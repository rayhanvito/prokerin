<?php

declare(strict_types=1);

namespace App\Actions\Report;

use App\Domain\Organization\OrganizationRole;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class ToggleLpjChecklistItemAction
{
    public function execute(int $actorUserId, int $itemId, bool $isComplete): void
    {
        $item = DB::table('lpj_checklist_items')
            ->join('projects', 'projects.id', '=', 'lpj_checklist_items.project_id')
            ->join('organization_members', 'organization_members.organization_id', '=', 'projects.organization_id')
            ->where('lpj_checklist_items.id', $itemId)
            ->where('organization_members.user_id', $actorUserId)
            ->whereIn('organization_members.role', [
                OrganizationRole::Owner->value,
                OrganizationRole::Admin->value,
                OrganizationRole::Secretary->value,
                OrganizationRole::Treasurer->value,
            ])
            ->whereIn('projects.status', ['running', 'lpj_review'])
            ->first([
                'lpj_checklist_items.id',
            ]);

        if ($item === null) {
            throw new NotFoundHttpException('LPJ checklist item was not found for the active workspace.');
        }

        DB::table('lpj_checklist_items')
            ->where('id', $itemId)
            ->update([
                'is_complete' => $isComplete,
                'updated_at' => now(),
            ]);
    }
}
