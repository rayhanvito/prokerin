<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DemoShowcaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

final class DemoShowcaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_demo_showcase_seeder_creates_clear_login_accounts_and_realistic_workspace_data(): void
    {
        $this->seed(DemoShowcaseSeeder::class);

        $owner = User::query()->where('email', 'demo.owner@prokerin.test')->firstOrFail();
        $organizationId = (int) DB::table('organizations')
            ->where('slug', 'bem-feb-nusantara-surabaya')
            ->value('id');
        $careerWeekId = (int) DB::table('projects')
            ->where('organization_id', $organizationId)
            ->where('slug', 'surabaya-career-week-2026')
            ->value('id');

        $this->assertTrue(Hash::check('demo12345', $owner->password));
        $this->assertDatabaseHas('organization_members', [
            'organization_id' => $organizationId,
            'user_id' => $owner->id,
            'role' => 'organization_owner',
        ]);
        $this->assertDatabaseHas('projects', [
            'id' => $careerWeekId,
            'name' => 'Surabaya Career Week 2026',
            'status' => 'running',
            'progress' => 68,
        ]);

        $this->assertGreaterThanOrEqual(8, DB::table('users')->where('email', 'like', 'demo.%@prokerin.test')->count());
        $this->assertGreaterThanOrEqual(4, DB::table('projects')->where('organization_id', $organizationId)->count());
        $this->assertGreaterThanOrEqual(8, DB::table('project_tasks')->where('project_id', $careerWeekId)->orWhereIn('project_id', function ($query) use ($organizationId): void {
            $query->select('id')->from('projects')->where('organization_id', $organizationId);
        })->count());
        $this->assertGreaterThanOrEqual(5, DB::table('documents')->where('organization_id', $organizationId)->count());
        $this->assertGreaterThanOrEqual(5, DB::table('event_registrations')->where('project_id', $careerWeekId)->count());
        $this->assertDatabaseHas('feature_flags', ['key' => 'ai_proposal_suggestion']);
    }
}
