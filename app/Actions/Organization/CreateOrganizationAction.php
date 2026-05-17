<?php

declare(strict_types=1);

namespace App\Actions\Organization;

use App\DTOs\Organization\CreateOrganizationData;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class CreateOrganizationAction
{
    public function execute(int $actorUserId, CreateOrganizationData $data): int
    {
        return DB::transaction(function () use ($actorUserId, $data): int {
            $now = now();
            $slug = $this->resolveSlug($data->slug ?: $data->name);
            $year = (int) $now->year;

            $organizationId = (int) DB::table('organizations')->insertGetId([
                'name' => $data->name,
                'slug' => $slug,
                'status' => 'active',
                'plan_tier' => $data->planTier,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('organization_members')->insert([
                'organization_id' => $organizationId,
                'user_id' => $actorUserId,
                'role' => 'organization_owner',
                'joined_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('organization_periods')->insert([
                'organization_id' => $organizationId,
                'name' => (string) $year,
                'starts_at' => "{$year}-01-01",
                'ends_at' => "{$year}-12-31",
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            return $organizationId;
        });
    }

    private function resolveSlug(string $source): string
    {
        $base = Str::slug($source);
        $base = $base === '' ? 'organisasi' : $base;

        if (! DB::table('organizations')->where('slug', $base)->exists()) {
            return $base;
        }

        do {
            $candidate = sprintf('%s-%02d', $base, random_int(1, 99));
        } while (DB::table('organizations')->where('slug', $candidate)->exists());

        return $candidate;
    }
}
