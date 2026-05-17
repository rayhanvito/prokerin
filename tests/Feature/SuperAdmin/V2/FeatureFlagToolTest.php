<?php

declare(strict_types=1);

namespace Tests\Feature\SuperAdmin\V2;

use App\Domain\Organization\Enums\PlanTier;
use App\Filament\Resources\FeatureFlags\FeatureFlagResource;
use App\Filament\Resources\FeatureFlags\Pages\CreateFeatureFlag;
use App\Filament\Resources\FeatureFlags\Pages\EditFeatureFlag;
use App\Models\FeatureFlag;
use App\Models\Organization;
use App\Models\User;
use App\Support\FeatureFlag as FeatureFlagHelper;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

final class FeatureFlagToolTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_super_admin_can_open_feature_flag_resource(): void
    {
        $superAdmin = User::query()->where('email', 'superadmin@prokerin.internal')->firstOrFail();

        $this->actingAs($superAdmin)
            ->get(FeatureFlagResource::getUrl())
            ->assertOk();
    }

    public function test_regular_user_cannot_open_feature_flag_resource(): void
    {
        $member = User::query()->where('email', 'member@prokerin.test')->firstOrFail();

        $this->actingAs($member)
            ->get(FeatureFlagResource::getUrl())
            ->assertForbidden();
    }

    public function test_feature_flag_helper_resolves_global_organization_and_plan_targets(): void
    {
        $freeOrganization = Organization::query()->where('slug', 'hima-informatika')->firstOrFail();
        $proOrganization = Organization::query()->where('slug', 'bem-fakultas-teknologi')->firstOrFail();

        FeatureFlag::query()->create([
            'key' => 'm22_payment',
            'is_enabled_globally' => false,
            'enabled_organization_ids' => [$freeOrganization->id],
            'enabled_plan_tiers' => [PlanTier::Pro->value],
            'description' => 'Payment rollout.',
        ]);

        $this->assertTrue(FeatureFlagHelper::isEnabled('m22_payment', $freeOrganization->id));
        $this->assertTrue(FeatureFlagHelper::isEnabled('m22_payment', $proOrganization->id));
        $this->assertFalse(FeatureFlagHelper::isEnabled('m22_payment'));
        $this->assertFalse(FeatureFlagHelper::isEnabled('unknown_flag', $proOrganization->id));

        FeatureFlag::query()
            ->where('key', 'm22_payment')
            ->firstOrFail()
            ->update([
                'is_enabled_globally' => true,
            ]);

        $this->assertTrue(FeatureFlagHelper::isEnabled('m22_payment'));
    }

    public function test_super_admin_can_create_and_edit_feature_flag(): void
    {
        $superAdmin = User::query()->where('email', 'superadmin@prokerin.internal')->firstOrFail();
        $organization = Organization::query()->where('slug', 'hima-informatika')->firstOrFail();

        $this->actingAs($superAdmin);

        Livewire::test(CreateFeatureFlag::class)
            ->fillForm([
                'key' => 'm23_ai_assistant',
                'description' => 'AI assistant rollout.',
                'is_enabled_globally' => false,
                'enabled_organization_ids' => [$organization->id],
                'enabled_plan_tiers' => [PlanTier::Pro->value],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $featureFlag = FeatureFlag::query()->where('key', 'm23_ai_assistant')->firstOrFail();

        $this->assertSame([$organization->id], $featureFlag->enabled_organization_ids);
        $this->assertSame([PlanTier::Pro->value], $featureFlag->enabled_plan_tiers);

        Livewire::test(EditFeatureFlag::class, ['record' => $featureFlag->getRouteKey()])
            ->fillForm([
                'key' => 'm23_ai_assistant',
                'description' => 'Global AI assistant rollout.',
                'is_enabled_globally' => true,
                'enabled_organization_ids' => [$organization->id],
                'enabled_plan_tiers' => [PlanTier::Pro->value],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertTrue($featureFlag->refresh()->is_enabled_globally);
    }
}
