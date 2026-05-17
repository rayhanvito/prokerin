<?php

declare(strict_types=1);

namespace Tests\Feature\Onboarding;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class OnboardingWizardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_owner_with_completed_setup_does_not_see_wizard(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();

        Organization::query()
            ->where('slug', 'bem-fakultas-teknologi')
            ->update(['onboarding_completed_at' => now()]);

        $response = $this->actingAs($owner)->get(route('proker.index'));

        $response->assertOk();
        $response->assertInertia(
            fn ($page) => $page->where('onboarding.show', false)
        );
    }

    public function test_owner_of_fresh_org_sees_pending_wizard_steps(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();

        // Force fresh state: clear period/projects/budget for owner's org
        $orgId = Organization::query()->where('slug', 'bem-fakultas-teknologi')->value('id');
        DB::table('budget_lines')
            ->whereIn('project_id', DB::table('projects')->where('organization_id', $orgId)->pluck('id'))
            ->delete();
        DB::table('projects')->where('organization_id', $orgId)->delete();
        DB::table('organization_periods')->where('organization_id', $orgId)->delete();
        DB::table('organization_invitations')->where('organization_id', $orgId)->delete();
        DB::table('organization_members')->where('organization_id', $orgId)->where('user_id', '!=', $owner->id)->delete();
        Organization::query()->where('id', $orgId)->update([
            'onboarding_completed_at' => null,
            'onboarding_step' => 1,
            'onboarding_skipped' => false,
        ]);

        $response = $this->actingAs($owner)->get(route('proker.index'));

        $response->assertOk();
        $response->assertInertia(
            fn ($page) => $page
                ->where('onboarding.show', true)
                ->where('onboarding.currentStep', 1)
                ->where('onboarding.completedSteps', [])
                ->where('onboarding.steps.0.key', 'period')
                ->where('onboarding.steps.0.complete', false)
                ->where('onboarding.steps.1.key', 'invite')
                ->where('onboarding.steps.1.complete', false)
                ->where('onboarding.steps.2.key', 'project')
                ->where('onboarding.steps.2.complete', false)
        );
    }

    public function test_non_owner_member_does_not_get_wizard(): void
    {
        $member = User::query()->where('email', 'member@prokerin.test')->firstOrFail();

        $response = $this->actingAs($member)->get(route('proker.index'));

        $response->assertOk();
        $response->assertInertia(
            fn ($page) => $page->where('onboarding', null)
        );
    }

    public function test_complete_endpoint_marks_onboarding_done(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();

        $response = $this->actingAs($owner)->post(route('onboarding.complete'));

        $response->assertRedirect();

        $this->assertNotNull(
            Organization::query()->where('slug', 'bem-fakultas-teknologi')->value('onboarding_completed_at'),
        );
    }

    public function test_complete_step_endpoint_advances_current_step(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();
        $orgId = Organization::query()->where('slug', 'bem-fakultas-teknologi')->value('id');

        Organization::query()->where('id', $orgId)->update([
            'onboarding_completed_at' => null,
            'onboarding_step' => 1,
            'onboarding_skipped' => false,
        ]);

        $this->actingAs($owner)
            ->post(route('onboarding.steps.complete', ['step' => 1]))
            ->assertRedirect();

        $this->assertDatabaseHas('organizations', [
            'id' => $orgId,
            'onboarding_step' => 2,
            'onboarding_skipped' => false,
        ]);
    }

    public function test_complete_step_five_marks_onboarding_done(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();
        $orgId = Organization::query()->where('slug', 'bem-fakultas-teknologi')->value('id');

        Organization::query()->where('id', $orgId)->update([
            'onboarding_completed_at' => null,
            'onboarding_step' => 5,
            'onboarding_skipped' => false,
        ]);

        $this->actingAs($owner)
            ->post(route('onboarding.steps.complete', ['step' => 5]))
            ->assertRedirect();

        $this->assertNotNull(
            Organization::query()->where('id', $orgId)->value('onboarding_completed_at'),
        );
    }

    public function test_skip_endpoint_hides_onboarding_permanently(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();
        $orgId = Organization::query()->where('slug', 'bem-fakultas-teknologi')->value('id');

        Organization::query()->where('id', $orgId)->update([
            'onboarding_completed_at' => null,
            'onboarding_step' => 1,
            'onboarding_skipped' => false,
        ]);

        $this->actingAs($owner)
            ->post(route('onboarding.skip'))
            ->assertRedirect();

        $this->assertDatabaseHas('organizations', [
            'id' => $orgId,
            'onboarding_skipped' => true,
        ]);

        $this->actingAs($owner)
            ->get(route('proker.index'))
            ->assertInertia(fn ($page) => $page->where('onboarding.show', false));
    }

    public function test_existing_period_auto_marks_first_step_complete(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();
        $orgId = Organization::query()->where('slug', 'bem-fakultas-teknologi')->value('id');

        DB::table('projects')->where('organization_id', $orgId)->delete();
        DB::table('organization_invitations')->where('organization_id', $orgId)->delete();
        DB::table('organization_members')->where('organization_id', $orgId)->where('user_id', '!=', $owner->id)->delete();
        Organization::query()->where('id', $orgId)->update([
            'onboarding_completed_at' => null,
            'onboarding_step' => 1,
            'onboarding_skipped' => false,
        ]);

        $this->actingAs($owner)
            ->get(route('proker.index'))
            ->assertInertia(fn ($page) => $page
                ->where('onboarding.show', true)
                ->where('onboarding.steps.0.complete', true)
                ->where('onboarding.completedSteps.0', 1));
    }

    public function test_non_owner_cannot_complete_onboarding(): void
    {
        $member = User::query()->where('email', 'member@prokerin.test')->firstOrFail();

        $response = $this->actingAs($member)->post(route('onboarding.complete'));

        $response->assertForbidden();
    }
}
