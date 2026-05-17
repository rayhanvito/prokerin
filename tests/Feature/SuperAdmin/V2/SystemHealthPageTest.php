<?php

declare(strict_types=1);

namespace Tests\Feature\SuperAdmin\V2;

use App\Actions\Admin\GetPlatformHealthAction;
use App\Filament\Pages\SystemHealthPage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class SystemHealthPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_super_admin_can_open_system_health_page(): void
    {
        $superAdmin = User::query()->where('email', 'superadmin@prokerin.internal')->firstOrFail();

        $this->actingAs($superAdmin)
            ->get(SystemHealthPage::getUrl())
            ->assertOk()
            ->assertSee('System Health')
            ->assertSee('Database')
            ->assertSee('Queue');
    }

    public function test_regular_user_cannot_open_system_health_page(): void
    {
        $member = User::query()->where('email', 'member@prokerin.test')->firstOrFail();

        $this->actingAs($member)
            ->get(SystemHealthPage::getUrl())
            ->assertForbidden();
    }

    public function test_system_health_page_can_refresh_services(): void
    {
        $superAdmin = User::query()->where('email', 'superadmin@prokerin.internal')->firstOrFail();

        $this->actingAs($superAdmin);

        Livewire::test(SystemHealthPage::class)
            ->assertSet('health.database.status', app(GetPlatformHealthAction::class)->execute()['database']['status'])
            ->call('refreshHealth')
            ->assertSet('health.queue.status', app(GetPlatformHealthAction::class)->execute()['queue']['status']);
    }
}
