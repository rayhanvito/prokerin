<?php

declare(strict_types=1);

namespace Tests\Unit\SuperAdmin;

use App\Actions\SuperAdmin\LogActivityAction;
use App\Models\ActivityLog;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class LogActivityActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_activity_log_with_correct_action_and_target(): void
    {
        $actor = User::factory()->create();
        $organization = Organization::query()->create([
            'name' => 'BEM Test',
            'slug' => 'bem-test',
            'status' => 'active',
            'plan_tier' => 'free',
        ]);

        $this->actingAs($actor);

        $log = (new LogActivityAction)->execute('org.plan_tier.change', $organization, [
            'before' => 'free',
            'after' => 'pro',
        ]);

        $this->assertInstanceOf(ActivityLog::class, $log);
        $this->assertSame('org.plan_tier.change', $log->action);
        $this->assertSame(Organization::class, $log->target_type);
        $this->assertSame($organization->id, $log->target_id);
        $this->assertSame($actor->id, $log->user_id);
        $this->assertSame(['before' => 'free', 'after' => 'pro'], $log->payload);
    }

    public function test_it_records_authenticated_user_id(): void
    {
        $actor = User::factory()->create();
        $target = User::factory()->create();

        $this->actingAs($actor);

        $log = (new LogActivityAction)->execute('user.delete', $target);

        $this->assertSame($actor->id, $log->user_id);
        $this->assertSame($target->id, $log->target_id);
        $this->assertSame(User::class, $log->target_type);
    }

    public function test_it_stores_null_payload_when_none_provided(): void
    {
        $actor = User::factory()->create();
        $target = User::factory()->create();

        $this->actingAs($actor);

        $log = (new LogActivityAction)->execute('user.delete', $target);

        $this->assertNull($log->payload);
    }

    public function test_it_records_ip_address_and_user_agent(): void
    {
        $actor = User::factory()->create();
        $target = User::factory()->create();

        $this->actingAs($actor);

        $log = (new LogActivityAction)->execute('user.delete', $target);

        $this->assertNotNull($log);
        $this->assertSame('127.0.0.1', $log->ip_address);
    }
}
