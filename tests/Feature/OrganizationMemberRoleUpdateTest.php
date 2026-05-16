<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Actions\Dashboard\SidebarMenuAction;
use App\Domain\Organization\OrganizationRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class OrganizationMemberRoleUpdateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_owner_can_update_member_role_in_their_organization(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();
        $member = User::query()->where('email', 'member@prokerin.test')->firstOrFail();
        $membershipId = $this->membershipId($member->id);

        $this->actingAs($owner)
            ->patch(route('members.role.update', $membershipId), [
                'role' => OrganizationRole::Secretary->value,
            ])
            ->assertRedirect()
            ->assertSessionHas('success', 'Role anggota berhasil diperbarui.');

        $this->assertDatabaseHas('organization_members', [
            'id' => $membershipId,
            'role' => OrganizationRole::Secretary->value,
        ]);
    }

    public function test_promoted_treasurer_sees_finance_sidebar_menu(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();
        $member = User::query()->where('email', 'member@prokerin.test')->firstOrFail();
        $membershipId = $this->membershipId($member->id);

        $this->actingAs($owner)
            ->patch(route('members.role.update', $membershipId), [
                'role' => OrganizationRole::Treasurer->value,
            ])
            ->assertRedirect();

        $organizationId = (int) DB::table('organization_members')
            ->where('id', $membershipId)
            ->value('organization_id');

        $menu = app(SidebarMenuAction::class)->execute($member, $organizationId);
        $labels = array_values(array_map(
            static fn (array $item): string => (string) $item['label'],
            array_merge(...array_column($menu, 'items')),
        ));

        $this->assertContains('RAB & Keuangan', $labels);
    }

    public function test_regular_member_cannot_update_roles(): void
    {
        $actor = User::query()->where('email', 'member@prokerin.test')->firstOrFail();
        $viewer = User::query()->where('email', 'viewer@prokerin.test')->firstOrFail();

        $this->actingAs($actor)
            ->patch(route('members.role.update', $this->membershipId($viewer->id)), [
                'role' => OrganizationRole::Treasurer->value,
            ])
            ->assertForbidden();
    }

    public function test_admin_cannot_assign_owner_role(): void
    {
        $admin = User::query()->where('email', 'admin@prokerin.test')->firstOrFail();
        $member = User::query()->where('email', 'member@prokerin.test')->firstOrFail();

        $this->actingAs($admin)
            ->patch(route('members.role.update', $this->membershipId($member->id)), [
                'role' => OrganizationRole::Owner->value,
            ])
            ->assertForbidden();
    }

    public function test_last_owner_cannot_be_demoted(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();

        $this->actingAs($owner)
            ->patch(route('members.role.update', $this->membershipId($owner->id)), [
                'role' => OrganizationRole::Admin->value,
            ])
            ->assertSessionHasErrors('role');

        $this->assertDatabaseHas('organization_members', [
            'id' => $this->membershipId($owner->id),
            'role' => OrganizationRole::Owner->value,
        ]);
    }

    private function membershipId(int $userId): int
    {
        $organizationId = DB::table('organizations')
            ->where('slug', 'bem-fakultas-teknologi')
            ->value('id');

        return (int) DB::table('organization_members')
            ->where('organization_id', $organizationId)
            ->where('user_id', $userId)
            ->value('id');
    }
}
