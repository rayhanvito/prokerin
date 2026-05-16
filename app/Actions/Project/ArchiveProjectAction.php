<?php

declare(strict_types=1);

namespace App\Actions\Project;

use App\Domain\Project\ProjectStatus;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class ArchiveProjectAction
{
    public function execute(int $actorUserId, string $projectSlug): void
    {
        $project = DB::table('projects')
            ->where('slug', $projectSlug)
            ->whereIn('organization_id', $this->archivableOrganizationIds($actorUserId))
            ->first();

        if ($project === null) {
            throw new NotFoundHttpException('Project was not found for the active workspace.');
        }

        DB::table('projects')
            ->where('id', $project->id)
            ->update([
                'status' => ProjectStatus::Archived->value,
                'updated_at' => now(),
            ]);
    }

    /**
     * @return array<int, int>
     */
    private function archivableOrganizationIds(int $actorUserId): array
    {
        return DB::table('organization_members')
            ->where('user_id', $actorUserId)
            ->whereIn('role', ['organization_owner', 'organization_admin'])
            ->pluck('organization_id')
            ->map(static fn (mixed $id): int => (int) $id)
            ->all();
    }
}
