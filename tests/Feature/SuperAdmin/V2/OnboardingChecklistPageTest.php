<?php

declare(strict_types=1);

namespace Tests\Feature\SuperAdmin\V2;

use App\Actions\Admin\GetOnboardingChecklistAction;
use App\Filament\Pages\OnboardingChecklistPage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class OnboardingChecklistPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_super_admin_can_open_onboarding_checklist_page(): void
    {
        $superAdmin = User::query()->where('email', 'superadmin@prokerin.internal')->firstOrFail();

        $this->actingAs($superAdmin)
            ->get(OnboardingChecklistPage::getUrl())
            ->assertOk()
            ->assertSee('Onboarding Checklist')
            ->assertSee('BEM Fakultas Teknologi')
            ->assertSee('Logo uploaded');
    }

    public function test_regular_user_cannot_open_onboarding_checklist_page(): void
    {
        $member = User::query()->where('email', 'member@prokerin.test')->firstOrFail();

        $this->actingAs($member)
            ->get(OnboardingChecklistPage::getUrl())
            ->assertForbidden();
    }

    public function test_onboarding_checklist_action_returns_expected_items(): void
    {
        $rows = app(GetOnboardingChecklistAction::class)->execute();

        $this->assertNotEmpty($rows);
        $this->assertArrayHasKey('Logo uploaded', $rows[0]['checklist']);
        $this->assertArrayHasKey('Member invited', $rows[0]['checklist']);
        $this->assertArrayHasKey('Active period set', $rows[0]['checklist']);
        $this->assertArrayHasKey('First proker created', $rows[0]['checklist']);
        $this->assertArrayHasKey('First proposal submitted', $rows[0]['checklist']);
    }

    public function test_onboarding_checklist_page_can_refresh_rows(): void
    {
        $superAdmin = User::query()->where('email', 'superadmin@prokerin.internal')->firstOrFail();

        $this->actingAs($superAdmin);

        Livewire::test(OnboardingChecklistPage::class)
            ->assertSet('organizations.0.name', app(GetOnboardingChecklistAction::class)->execute()[0]['name'])
            ->call('refreshChecklist')
            ->assertSet('organizations.0.total_count', 5);
    }
}
