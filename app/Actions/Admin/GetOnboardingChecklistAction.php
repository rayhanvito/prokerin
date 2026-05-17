<?php

declare(strict_types=1);

namespace App\Actions\Admin;

use App\Models\Organization;
use Illuminate\Support\Facades\DB;

final class GetOnboardingChecklistAction
{
    /**
     * @return list<array{
     *     id: int,
     *     name: string,
     *     slug: string,
     *     plan_tier: string,
     *     created_at: string,
     *     checklist: array<string, bool>,
     *     completed_count: int,
     *     total_count: int
     * }>
     */
    public function execute(): array
    {
        return Organization::query()
            ->where('created_at', '>=', now()->subDays(30))
            ->orderByDesc('created_at')
            ->limit(50)
            ->get()
            ->map(fn (Organization $organization): array => $this->row($organization))
            ->values()
            ->all();
    }

    /**
     * @return array{
     *     id: int,
     *     name: string,
     *     slug: string,
     *     plan_tier: string,
     *     created_at: string,
     *     checklist: array<string, bool>,
     *     completed_count: int,
     *     total_count: int
     * }
     */
    private function row(Organization $organization): array
    {
        $checklist = [
            'Logo uploaded' => filled($organization->logo_path),
            'Member invited' => DB::table('organization_invitations')
                ->where('organization_id', $organization->id)
                ->exists(),
            'Active period set' => DB::table('organization_periods')
                ->where('organization_id', $organization->id)
                ->where('is_active', true)
                ->exists(),
            'First proker created' => DB::table('projects')
                ->where('organization_id', $organization->id)
                ->exists(),
            'First proposal submitted' => DB::table('proposal_drafts')
                ->join('projects', 'projects.id', '=', 'proposal_drafts.project_id')
                ->where('projects.organization_id', $organization->id)
                ->whereIn('proposal_drafts.status', ['submitted', 'approved'])
                ->exists(),
        ];

        return [
            'id' => (int) $organization->id,
            'name' => (string) $organization->name,
            'slug' => (string) $organization->slug,
            'plan_tier' => (string) $organization->getRawOriginal('plan_tier'),
            'created_at' => (string) $organization->created_at?->format('Y-m-d'),
            'checklist' => $checklist,
            'completed_count' => collect($checklist)->filter()->count(),
            'total_count' => count($checklist),
        ];
    }
}
