<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class OrganizationCreateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_authenticated_user_can_create_organization_with_generated_slug(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('organization.store'), [
                'name' => 'BEM Fakultas Kedokteran',
            ])
            ->assertRedirect(route('organization.setup'))
            ->assertSessionHas('success', 'Organisasi berhasil dibuat.')
            ->assertSessionHas('active_organization_id');

        $organization = DB::table('organizations')
            ->where('slug', 'bem-fakultas-kedokteran')
            ->first();

        $this->assertNotNull($organization);
        $this->assertSame('free', (string) $organization->plan_tier);
        $this->assertDatabaseHas('organization_members', [
            'organization_id' => (int) $organization->id,
            'user_id' => $user->id,
            'role' => 'organization_owner',
        ]);
        $this->assertDatabaseHas('organization_periods', [
            'organization_id' => (int) $organization->id,
            'name' => (string) now()->year,
            'starts_at' => now()->year.'-01-01',
            'ends_at' => now()->year.'-12-31',
            'is_active' => true,
        ]);
    }

    public function test_blank_slug_collision_gets_unique_generated_suffix(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('organization.store'), [
                'name' => 'BEM Fakultas Teknologi',
            ])
            ->assertRedirect(route('organization.setup'));

        $this->assertDatabaseHas('organizations', [
            'name' => 'BEM Fakultas Teknologi',
        ]);
        $this->assertTrue(
            DB::table('organizations')
                ->where('name', 'BEM Fakultas Teknologi')
                ->where('slug', 'like', 'bem-fakultas-teknologi-%')
                ->exists(),
        );
    }

    public function test_duplicate_provided_slug_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('organization.store'), [
                'name' => 'Organisasi Baru',
                'slug' => 'bem-fakultas-teknologi',
            ])
            ->assertSessionHasErrors('slug');
    }

    public function test_invalid_slug_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('organization.store'), [
                'name' => 'Organisasi Baru',
                'slug' => 'Slug Tidak Valid',
            ])
            ->assertSessionHasErrors('slug');
    }

    public function test_guest_is_redirected_to_login_when_creating_organization(): void
    {
        $this->post(route('organization.store'), [
            'name' => 'Organisasi Baru',
        ])->assertRedirect('/login');
    }
}
