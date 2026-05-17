<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class FeatureFlagSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_creates_ai_feature_flags_disabled_by_default(): void
    {
        $this->seed();

        foreach (['ai_proposal_suggestion', 'ai_lpj_summary'] as $key) {
            $this->assertDatabaseHas('feature_flags', [
                'key' => $key,
                'is_enabled_globally' => false,
            ]);
        }
    }
}
