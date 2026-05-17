<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Project\ProjectRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

final class ProjectMembersManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_project_detail_includes_project_members_and_available_org_members(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();

        $this->actingAs($owner)
            ->get(route('proker.detail', ['project' => 'seminar-karier-digital']))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Proker/Show')
                ->has('projectMembers', 4)
                ->where('projectMembers.0.role', ProjectRole::ProjectLead->value)
                ->has('availableMembers', 11));
    }

    public function test_owner_can_assign_and_remove_project_member(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();
        $user = User::query()->where('email', 'treasurer@prokerin.test')->firstOrFail();

        $this->actingAs($owner)
            ->post(route('proker.members.store', ['project' => 'seminar-karier-digital']), [
                'user_id' => $user->id,
                'role' => ProjectRole::CommitteeMember->value,
            ])
            ->assertRedirect()
            ->assertSessionHas('success', 'Anggota tim proker berhasil ditambahkan.');

        $projectMemberId = (int) DB::table('project_members')
            ->where('user_id', $user->id)
            ->where('project_id', $this->projectId('seminar-karier-digital'))
            ->value('id');

        $this->assertDatabaseHas('project_members', [
            'id' => $projectMemberId,
            'role' => ProjectRole::CommitteeMember->value,
        ]);

        $this->actingAs($owner)
            ->delete(route('proker.members.destroy', [
                'project' => 'seminar-karier-digital',
                'member' => $projectMemberId,
            ]))
            ->assertRedirect()
            ->assertSessionHas('success', 'Anggota tim proker berhasil dihapus.');

        $this->assertDatabaseMissing('project_members', [
            'id' => $projectMemberId,
        ]);
    }

    public function test_cannot_assign_user_from_other_organization(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();
        $owner2 = User::query()->where('email', 'owner2@prokerin.test')->firstOrFail();

        $this->actingAs($owner)
            ->post(route('proker.members.store', ['project' => 'seminar-karier-digital']), [
                'user_id' => $owner2->id,
                'role' => ProjectRole::Viewer->value,
            ])
            ->assertSessionHasErrors('user_id');
    }

    public function test_member_cannot_manage_project_members(): void
    {
        $member = User::query()->where('email', 'member@prokerin.test')->firstOrFail();
        $treasurer = User::query()->where('email', 'treasurer@prokerin.test')->firstOrFail();

        $this->actingAs($member)
            ->post(route('proker.members.store', ['project' => 'seminar-karier-digital']), [
                'user_id' => $treasurer->id,
                'role' => ProjectRole::CommitteeMember->value,
            ])
            ->assertForbidden();
    }

    public function test_project_lead_cannot_be_removed(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();
        $leadMemberId = (int) DB::table('project_members')
            ->where('project_id', $this->projectId('seminar-karier-digital'))
            ->where('role', ProjectRole::ProjectLead->value)
            ->value('id');

        $this->actingAs($owner)
            ->delete(route('proker.members.destroy', [
                'project' => 'seminar-karier-digital',
                'member' => $leadMemberId,
            ]))
            ->assertForbidden();
    }

    private function projectId(string $slug): int
    {
        return (int) DB::table('projects')
            ->where('slug', $slug)
            ->value('id');
    }
}
