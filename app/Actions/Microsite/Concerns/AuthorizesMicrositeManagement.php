<?php

declare(strict_types=1);

namespace App\Actions\Microsite\Concerns;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

trait AuthorizesMicrositeManagement
{
    /**
     * @return object{id: int, organization_id: int, name: string, slug: string, organization_slug: string, organization_role: string|null}
     *
     * @throws AuthorizationException
     */
    private function authorizeProjectManager(int $actorUserId, string $projectSlug): object
    {
        $project = DB::table('projects')
            ->join('organizations', 'organizations.id', '=', 'projects.organization_id')
            ->leftJoin('organization_members', function ($join) use ($actorUserId): void {
                $join->on('organization_members.organization_id', '=', 'projects.organization_id')
                    ->where('organization_members.user_id', $actorUserId);
            })
            ->where('projects.slug', $projectSlug)
            ->first([
                'projects.id',
                'projects.organization_id',
                'projects.name',
                'projects.slug',
                'organizations.slug as organization_slug',
                'organization_members.role as organization_role',
            ]);

        if ($project === null) {
            throw new NotFoundHttpException('Proker tidak ditemukan.');
        }

        if (! in_array((string) $project->organization_role, ['organization_owner', 'organization_admin', 'secretary'], true)) {
            throw new AuthorizationException('Anda tidak berhak mengelola microsite proker ini.');
        }

        return $project;
    }

    private function ensureMicrosite(int $projectId, int $actorUserId): int
    {
        $now = now();

        DB::table('proker_microsites')->updateOrInsert(
            ['project_id' => $projectId],
            [
                'updated_by_user_id' => $actorUserId,
                'updated_at' => $now,
                'created_at' => $now,
            ],
        );

        return (int) DB::table('proker_microsites')
            ->where('project_id', $projectId)
            ->value('id');
    }
}
