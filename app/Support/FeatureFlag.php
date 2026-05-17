<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\FeatureFlag as FeatureFlagModel;
use App\Models\Organization;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

final class FeatureFlag
{
    public static function isEnabled(string $key, ?int $organizationId = null): bool
    {
        $flag = self::flags()->get($key);

        if (! $flag instanceof FeatureFlagModel) {
            return false;
        }

        if ($flag->is_enabled_globally) {
            return true;
        }

        if ($organizationId === null) {
            return false;
        }

        $organizationIds = collect($flag->enabled_organization_ids ?? [])
            ->map(static fn (mixed $id): int => (int) $id)
            ->all();

        if (in_array($organizationId, $organizationIds, true)) {
            return true;
        }

        $planTiers = collect($flag->enabled_plan_tiers ?? [])
            ->map(static fn (mixed $tier): string => (string) $tier)
            ->all();

        if ($planTiers === []) {
            return false;
        }

        $organization = Organization::query()
            ->select(['id', 'plan_tier'])
            ->find($organizationId);

        if (! $organization instanceof Organization) {
            return false;
        }

        return in_array($organization->plan_tier->value, $planTiers, true);
    }

    /**
     * @return Collection<string, FeatureFlagModel>
     */
    private static function flags(): Collection
    {
        return Cache::remember(
            FeatureFlagModel::CACHE_KEY,
            now()->addMinutes(5),
            static fn (): Collection => FeatureFlagModel::query()
                ->get()
                ->keyBy('key')
        );
    }
}
