<?php

declare(strict_types=1);

namespace App\Actions\EventRegistration;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class UpdateEventRegistrationSettingsAction
{
    /**
     * @param  array{is_open: bool, capacity?: int|null, opens_at?: string|null, closes_at?: string|null, require_payment: bool}  $data
     *
     * @throws AuthorizationException
     */
    public function execute(int $actorUserId, int $projectId, array $data): void
    {
        $project = DB::table('projects')
            ->leftJoin('organization_members', function ($join) use ($actorUserId): void {
                $join->on('organization_members.organization_id', '=', 'projects.organization_id')
                    ->where('organization_members.user_id', $actorUserId);
            })
            ->leftJoin('project_members', function ($join) use ($actorUserId): void {
                $join->on('project_members.project_id', '=', 'projects.id')
                    ->where('project_members.user_id', $actorUserId);
            })
            ->where('projects.id', $projectId)
            ->first([
                'projects.id',
                'organization_members.role as organization_role',
                'project_members.role as project_role',
            ]);

        if ($project === null) {
            throw new NotFoundHttpException('Event was not found for this workspace.');
        }

        $canManage = in_array((string) $project->organization_role, ['organization_owner', 'organization_admin', 'secretary'], true)
            || in_array((string) $project->project_role, ['project_lead'], true);

        if (! $canManage) {
            throw new AuthorizationException('You are not allowed to manage event registration settings.');
        }

        DB::table('event_registration_settings')->updateOrInsert(
            ['project_id' => $projectId],
            [
                'is_open' => $data['is_open'],
                'capacity' => $data['capacity'] ?? null,
                'opens_at' => $data['opens_at'] ?? null,
                'closes_at' => $data['closes_at'] ?? null,
                'require_payment' => $data['require_payment'],
                'updated_at' => now(),
                'created_at' => now(),
            ],
        );
    }
}
