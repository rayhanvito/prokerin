<?php

declare(strict_types=1);

namespace App\Actions\Project;

use App\Domain\Project\ProjectTemplateType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class UpdateProjectAction
{
    /**
     * @return array{slug: string}
     *
     * @throws ValidationException
     */
    public function execute(
        int $actorUserId,
        string $projectSlug,
        string $name,
        ?string $description,
        ProjectTemplateType $templateType,
        string $startsAt,
        string $endsAt,
        ?int $projectLeadId = null,
    ): array {
        $project = DB::table('projects')
            ->where('slug', $projectSlug)
            ->whereIn('organization_id', $this->manageableOrganizationIds($actorUserId))
            ->first();

        if ($project === null) {
            throw new NotFoundHttpException('Project was not found for the active workspace.');
        }

        $organizationId = (int) $project->organization_id;
        $leadUserId = $projectLeadId ?? (int) $project->project_lead_id;

        $leadBelongsToOrganization = DB::table('organization_members')
            ->where('organization_id', $organizationId)
            ->where('user_id', $leadUserId)
            ->exists();

        if (! $leadBelongsToOrganization) {
            throw ValidationException::withMessages([
                'project_lead_id' => 'Project lead must belong to the project organization.',
            ]);
        }

        $newSlug = $this->uniqueSlug($organizationId, $name, (int) $project->id);
        $templateId = DB::table('project_templates')
            ->where('type', $templateType->value)
            ->value('id');

        DB::table('projects')
            ->where('id', $project->id)
            ->update([
                'project_template_id' => $templateId,
                'project_lead_id' => $leadUserId,
                'name' => $name,
                'slug' => $newSlug,
                'description' => $description,
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'updated_at' => now(),
            ]);

        DB::table('project_members')->updateOrInsert(
            [
                'project_id' => (int) $project->id,
                'user_id' => $leadUserId,
            ],
            [
                'role' => 'project_lead',
                'updated_at' => now(),
                'created_at' => now(),
            ],
        );

        return ['slug' => $newSlug];
    }

    /**
     * @return array<int, int>
     */
    private function manageableOrganizationIds(int $actorUserId): array
    {
        return DB::table('organization_members')
            ->where('user_id', $actorUserId)
            ->whereIn('role', ['organization_owner', 'organization_admin', 'secretary'])
            ->pluck('organization_id')
            ->map(static fn (mixed $id): int => (int) $id)
            ->all();
    }

    private function uniqueSlug(int $organizationId, string $name, int $ignoreProjectId): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $suffix = 2;

        while (
            DB::table('projects')
                ->where('organization_id', $organizationId)
                ->where('slug', $slug)
                ->where('id', '!=', $ignoreProjectId)
                ->exists()
        ) {
            $slug = "{$baseSlug}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
}
