<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Actions\Dashboard\SidebarMenuAction;
use App\Actions\Organization\CreateKepanitiaanAction;
use App\Jobs\AutoArchiveKepanitiaanJob;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

final class KepanitiaanModeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->seed();
        Cache::flush();
    }

    public function test_owner_can_create_kepanitiaan_workspace_without_period(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();
        $eventDate = now()->addMonth()->toDateString();

        $this->actingAs($owner)
            ->post(route('organization.kepanitiaan.store'), [
                'name' => 'Panitia Dies Natalis',
                'event_date' => $eventDate,
                'description' => 'Event tahunan kampus.',
            ])
            ->assertRedirect(route('dashboard'));

        $organizationId = (int) DB::table('organizations')
            ->where('slug', 'panitia-dies-natalis')
            ->value('id');

        $this->assertGreaterThan(0, $organizationId);
        $this->assertDatabaseHas('organizations', [
            'id' => $organizationId,
            'mode' => 'kepanitiaan',
            'event_date' => $eventDate,
            'status' => 'active',
        ]);
        $this->assertDatabaseMissing('organization_periods', [
            'organization_id' => $organizationId,
        ]);
        $this->assertDatabaseHas('organization_members', [
            'organization_id' => $organizationId,
            'user_id' => $owner->id,
            'role' => 'organization_owner',
        ]);
    }

    public function test_kepanitiaan_dashboard_replaces_role_dashboard(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();
        $organizationId = app(CreateKepanitiaanAction::class)->execute(
            actorUserId: (int) $owner->id,
            name: 'Panitia Expo Kampus',
            eventDate: CarbonImmutable::parse(now()->addWeeks(2)->toDateString()),
            description: 'Expo komunitas mahasiswa.',
        );

        $this->actingAs($owner)
            ->withSession(['active_organization_id' => $organizationId])
            ->get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn (Assert $page): Assert => $page
                ->component('KepanitiaanDashboard/Index')
                ->where('payload.organization.name', 'Panitia Expo Kampus')
                ->where('payload.metrics.projectCount', 0)
            );
    }

    public function test_kepanitiaan_hides_period_handover_and_role_matrix(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();
        $organizationId = app(CreateKepanitiaanAction::class)->execute(
            actorUserId: (int) $owner->id,
            name: 'Panitia Festival Riset',
            eventDate: CarbonImmutable::parse(now()->addMonths(2)->toDateString()),
        );

        $labels = array_values(array_map(
            static fn (array $item): string => (string) $item['label'],
            array_merge(...array_column(
                app(SidebarMenuAction::class)->execute($owner, $organizationId),
                'items',
            )),
        ));

        $this->assertNotContains('Periode', $labels);
        $this->assertNotContains('Handover', $labels);
        $this->assertNotContains('Anggota & Role', $labels);

        $this->actingAs($owner)
            ->withSession(['active_organization_id' => $organizationId])
            ->get(route('organization.periods'))
            ->assertForbidden();

        $this->actingAs($owner)
            ->withSession(['active_organization_id' => $organizationId])
            ->get(route('organization.handover'))
            ->assertForbidden();

        $this->actingAs($owner)
            ->withSession(['active_organization_id' => $organizationId])
            ->get(route('members.roles'))
            ->assertForbidden();
    }

    public function test_auto_archive_job_only_archives_due_kepanitiaan_workspaces(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();
        $dueId = app(CreateKepanitiaanAction::class)->execute(
            actorUserId: (int) $owner->id,
            name: 'Panitia Lama',
            eventDate: CarbonImmutable::parse(now()->subMonths(4)->toDateString()),
        );
        $futureId = app(CreateKepanitiaanAction::class)->execute(
            actorUserId: (int) $owner->id,
            name: 'Panitia Baru',
            eventDate: CarbonImmutable::parse(now()->addMonth()->toDateString()),
        );

        DB::table('organizations')->where('id', $dueId)->update([
            'auto_archive_at' => now()->subDay(),
        ]);

        app(AutoArchiveKepanitiaanJob::class)->handle();

        $this->assertDatabaseHas('organizations', [
            'id' => $dueId,
            'status' => 'archived',
        ]);
        $this->assertDatabaseHas('organizations', [
            'id' => $futureId,
            'status' => 'active',
        ]);
    }
}
