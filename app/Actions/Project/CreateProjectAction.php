<?php

declare(strict_types=1);

namespace App\Actions\Project;

use App\Domain\Project\ProjectRole;
use App\Domain\Project\ProjectStatus;
use App\Domain\Project\ProjectTemplateType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class CreateProjectAction
{
    /**
     * @return array{id: int, slug: string}
     *
     * @throws ValidationException
     */
    public function execute(
        int $actorUserId,
        string $name,
        ?string $description,
        ProjectTemplateType $templateType,
        string $startsAt,
        string $endsAt,
        ?int $projectLeadId = null,
    ): array {
        $actorMembership = DB::table('organization_members')
            ->where('user_id', $actorUserId)
            ->whereIn('role', ['organization_owner', 'organization_admin', 'secretary'])
            ->orderBy('id')
            ->first();

        if ($actorMembership === null) {
            throw ValidationException::withMessages([
                'organization' => 'User does not have an organization role that can create proker.',
            ]);
        }

        $organizationId = (int) $actorMembership->organization_id;
        $leadUserId = $projectLeadId ?? $actorUserId;

        $leadBelongsToOrganization = DB::table('organization_members')
            ->where('organization_id', $organizationId)
            ->where('user_id', $leadUserId)
            ->exists();

        if (! $leadBelongsToOrganization) {
            throw ValidationException::withMessages([
                'project_lead_id' => 'Project lead must belong to the active organization.',
            ]);
        }

        $templateId = DB::table('project_templates')
            ->where('type', $templateType->value)
            ->value('id');

        $periodId = DB::table('organization_periods')
            ->where('organization_id', $organizationId)
            ->where('is_active', true)
            ->orderByDesc('starts_at')
            ->value('id');

        $slug = $this->uniqueSlug($organizationId, $name);
        $now = now();

        $projectId = (int) DB::table('projects')->insertGetId([
            'organization_id' => $organizationId,
            'organization_period_id' => $periodId,
            'project_template_id' => $templateId,
            'project_lead_id' => $leadUserId,
            'name' => $name,
            'slug' => $slug,
            'description' => $description,
            'status' => ProjectStatus::Draft->value,
            'progress' => 0,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('project_members')->insert([
            'project_id' => $projectId,
            'user_id' => $leadUserId,
            'role' => ProjectRole::ProjectLead->value,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return [
            'id' => $projectId,
            'slug' => $slug,
        ];
    }

    private function uniqueSlug(int $organizationId, string $name): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $suffix = 2;

        while (DB::table('projects')->where('organization_id', $organizationId)->where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$suffix}";
            $suffix++;
        }

        return $slug;
    }
}
