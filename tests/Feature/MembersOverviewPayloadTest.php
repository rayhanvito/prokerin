<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

final class MembersOverviewPayloadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_members_overview_is_database_backed_and_tenant_scoped(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();

        $this->actingAs($owner)
            ->get(route('members.index'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Members/Index')
                ->where('metrics.0.label', 'Members')
                ->has('members', 11)
                ->where('members.0.role', 'organization_owner')
                ->where('members.0.email', 'owner@prokerin.test')
                ->has('roleBreakdown'));

        $owner2 = User::query()->where('email', 'owner2@prokerin.test')->firstOrFail();

        $this->actingAs($owner2)
            ->get(route('members.index'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Members/Index')
                ->has('members', 2)
                ->where('members.0.email', 'owner2@prokerin.test'));
    }

    public function test_owner_can_remove_member_but_not_last_owner(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();
        $memberId = (int) DB::table('organization_members')
            ->join('users', 'users.id', '=', 'organization_members.user_id')
            ->where('users.email', 'member@prokerin.test')
            ->value('organization_members.id');
        $ownerMembershipId = (int) DB::table('organization_members')
            ->where('user_id', $owner->id)
            ->value('id');

        $this->actingAs($owner)
            ->delete(route('organization.members.destroy', ['member' => $memberId]))
            ->assertRedirect()
            ->assertSessionHas('success', 'Anggota berhasil dihapus dari organisasi.');

        $this->assertDatabaseMissing('organization_members', [
            'id' => $memberId,
        ]);

        $this->actingAs($owner)
            ->delete(route('organization.members.destroy', ['member' => $ownerMembershipId]))
            ->assertForbidden();
    }

    public function test_non_owner_cannot_remove_member(): void
    {
        $admin = User::query()->where('email', 'admin@prokerin.test')->firstOrFail();
        $memberId = (int) DB::table('organization_members')
            ->join('users', 'users.id', '=', 'organization_members.user_id')
            ->where('users.email', 'member@prokerin.test')
            ->value('organization_members.id');

        $this->actingAs($admin)
            ->delete(route('organization.members.destroy', ['member' => $memberId]))
            ->assertForbidden();
    }
}
