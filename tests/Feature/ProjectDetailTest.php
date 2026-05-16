<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

final class ProjectDetailTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_project_detail_page_receives_database_backed_payload(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();

        $this->actingAs($owner)
            ->get(route('proker.detail', ['project' => 'seminar-karier-digital']))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Proker/Show')
                ->where('project.slug', 'seminar-karier-digital')
                ->where('project.organization', 'BEM Fakultas Teknologi')
                ->where('project.lead', 'Fajar Nugroho')
                ->has('metrics', 4)
                ->has('tasks', 4)
                ->where('tasks.0.task', 'Finalisasi proposal'));
    }

    public function test_project_detail_route_without_slug_uses_latest_accessible_project_for_backwards_compatibility(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();

        $this->actingAs($owner)
            ->get(route('proker.show'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Proker/Show')
                ->has('project.name')
                ->has('tasks'));
    }

    public function test_project_edit_page_receives_prefilled_project_payload(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();

        $this->actingAs($owner)
            ->get(route('proker.edit', ['project' => 'seminar-karier-digital']))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Proker/Edit')
                ->where('project.name', 'Seminar Karier Digital')
                ->where('project.slug', 'seminar-karier-digital')
                ->where('project.templateType', 'seminar')
                ->where('project.startsAt', '2026-06-12')
                ->where('project.endsAt', '2026-06-12'));
    }

    public function test_user_cannot_view_project_outside_their_organizations(): void
    {
        $member = User::query()->where('email', 'member@prokerin.test')->firstOrFail();

        $this->actingAs($member)
            ->get(route('proker.detail', ['project' => 'workshop-ui-ux-hmif']))
            ->assertNotFound();
    }
}
