<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\FeatureFlag;
use Illuminate\Database\Seeder;

final class FeatureFlagSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->flags() as $flag) {
            FeatureFlag::query()->updateOrCreate(
                ['key' => $flag['key']],
                $flag,
            );
        }
    }

    /**
     * @return list<array{
     *     key: string,
     *     is_enabled_globally: bool,
     *     enabled_organization_ids: null,
     *     enabled_plan_tiers: null,
     *     description: string
     * }>
     */
    private function flags(): array
    {
        return [
            [
                'key' => 'ai_proposal_suggestion',
                'is_enabled_globally' => false,
                'enabled_organization_ids' => null,
                'enabled_plan_tiers' => null,
                'description' => 'Saran AI untuk pengisian draft proposal.',
            ],
            [
                'key' => 'ai_lpj_summary',
                'is_enabled_globally' => false,
                'enabled_organization_ids' => null,
                'enabled_plan_tiers' => null,
                'description' => 'Ringkasan AI untuk LPJ.',
            ],
        ];
    }
}
