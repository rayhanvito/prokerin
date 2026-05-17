<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class GlobalSearchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->seed();
    }

    public function test_user_can_search_projects_by_keyword_with_tenant_scope(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();

        $this->actingAs($owner)
            ->getJson(route('search', ['q' => 'Seminar Karier']))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.projects.0.title', 'Seminar Karier Digital');
    }

    public function test_other_organization_result_is_not_visible(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();

        DB::table('projects')->insert([
            'organization_id' => $this->organizationId('hima-informatika'),
            'organization_period_id' => null,
            'project_template_id' => null,
            'project_lead_id' => null,
            'name' => 'Ospek Rahasia Org B',
            'slug' => 'ospek-rahasia-org-b',
            'description' => 'Tidak boleh muncul di org owner.',
            'status' => 'draft',
            'progress' => 0,
            'starts_at' => null,
            'ends_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($owner)->getJson(route('search', ['q' => 'Rahasia Org B']))->assertOk();

        $this->assertSame([], $response->json('data.projects'));
    }

    public function test_private_document_is_hidden_for_non_owner(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();
        $secretary = User::query()->where('email', 'sekretaris@prokerin.test')->firstOrFail();
        $organizationId = $this->organizationId('bem-fakultas-teknologi');

        DB::table('documents')->insert([
            'organization_id' => $organizationId,
            'project_id' => null,
            'owner_user_id' => $owner->id,
            'name' => 'Dokumen Privat Sponsor Ospek',
            'folder' => 'secretariat',
            'storage_path' => 'documents/private-sponsor.pdf',
            'mime_type' => 'application/pdf',
            'size_kb' => 128,
            'visibility' => 'private',
            'status' => 'uploaded',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($secretary)->getJson(route('search', ['q' => 'Privat Sponsor']))->assertOk();

        $this->assertSame([], $response->json('data.documents'));
    }

    public function test_member_result_is_limited_to_active_organization(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();
        $outsider = User::factory()->create(['name' => 'Nadia Searchable', 'email' => 'nadia-search@example.test']);

        DB::table('organization_members')->insert([
            'organization_id' => $this->organizationId('hima-informatika'),
            'user_id' => $outsider->id,
            'role' => 'member',
            'joined_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($owner)->getJson(route('search', ['q' => 'Nadia Searchable']))->assertOk();

        $this->assertSame([], $response->json('data.members'));
    }

    public function test_result_limit_is_five_per_category(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();
        $organizationId = $this->organizationId('bem-fakultas-teknologi');

        foreach (range(1, 7) as $index) {
            DB::table('projects')->insert([
                'organization_id' => $organizationId,
                'organization_period_id' => null,
                'project_template_id' => null,
                'project_lead_id' => null,
                'name' => "Ospek Search {$index}",
                'slug' => "ospek-search-{$index}",
                'description' => 'Batch result limit test.',
                'status' => 'draft',
                'progress' => 0,
                'starts_at' => null,
                'ends_at' => null,
                'created_at' => now(),
                'updated_at' => now()->addSeconds($index),
            ]);
        }

        $response = $this->actingAs($owner)->getJson(route('search', ['q' => 'Ospek Search']))->assertOk();

        $this->assertCount(5, $response->json('data.projects'));
    }

    private function organizationId(string $slug): int
    {
        return (int) DB::table('organizations')->where('slug', $slug)->value('id');
    }
}
