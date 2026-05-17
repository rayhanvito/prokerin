<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

final class MicrositeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
        $this->seed();
    }

    public function test_unpublished_microsite_returns_404_for_public_visitors(): void
    {
        $projectId = $this->projectId('seminar-karier-digital');

        DB::table('proker_microsites')->insert([
            'project_id' => $projectId,
            'is_published' => false,
            'description_md' => 'Event karier digital untuk mahasiswa.',
            'show_countdown' => true,
            'show_committee' => true,
            'show_gallery' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->get(route('microsites.show', [
            'orgSlug' => 'bem-fakultas-teknologi',
            'prokerSlug' => 'seminar-karier-digital',
        ]))->assertNotFound();
    }

    public function test_published_microsite_is_public_and_contains_seo_payload(): void
    {
        $this->publishMicrosite('seminar-karier-digital');

        $this->get(route('microsites.show', [
            'orgSlug' => 'bem-fakultas-teknologi',
            'prokerSlug' => 'seminar-karier-digital',
        ]))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Microsite/Show')
                ->where('project.name', 'Seminar Karier Digital')
                ->where('microsite.metaTitle', 'Seminar Karier Digital 2026')
                ->where('seo.title', 'Seminar Karier Digital 2026')
                ->where('registration.isAvailable', true)
                ->where('registration.url', route('events.register.show', ['project' => 'seminar-karier-digital'])));
    }

    public function test_owner_can_open_and_update_microsite_settings(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();

        $this->actingAs($owner)
            ->get(route('proker.microsite.edit', ['project' => 'seminar-karier-digital']))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Microsite/Settings')
                ->where('project.name', 'Seminar Karier Digital'));

        $this->actingAs($owner)
            ->patch(route('proker.microsite.update', ['project' => 'seminar-karier-digital']), [
                'description_md' => 'Microsite publik untuk Seminar Karier Digital.',
                'location_text' => 'Aula Kampus C',
                'location_maps_url' => 'https://maps.example.test/aula',
                'contact_name' => 'Sekretariat BEM',
                'contact_whatsapp' => '+6282112340000',
                'contact_email' => 'event@prokerin.test',
                'show_countdown' => true,
                'show_committee' => true,
                'show_gallery' => true,
                'meta_title' => 'Seminar Karier Digital 2026',
                'meta_description' => 'Daftar dan ikuti seminar karier digital untuk mahasiswa.',
            ])
            ->assertRedirect()
            ->assertSessionHas('success', 'Pengaturan microsite berhasil disimpan.');

        $this->assertDatabaseHas('proker_microsites', [
            'project_id' => $this->projectId('seminar-karier-digital'),
            'location_text' => 'Aula Kampus C',
            'meta_title' => 'Seminar Karier Digital 2026',
        ]);
    }

    public function test_member_from_other_organization_cannot_edit_microsite(): void
    {
        $outsider = User::factory()->create();

        DB::table('organization_members')->insert([
            'organization_id' => $this->organizationId('hima-informatika'),
            'user_id' => $outsider->id,
            'role' => 'organization_admin',
            'joined_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($outsider)
            ->patch(route('proker.microsite.update', ['project' => 'seminar-karier-digital']), [
                'description_md' => 'Cross org edit attempt.',
                'location_text' => null,
                'location_maps_url' => null,
                'contact_name' => null,
                'contact_whatsapp' => null,
                'contact_email' => null,
                'show_countdown' => true,
                'show_committee' => true,
                'show_gallery' => true,
                'meta_title' => null,
                'meta_description' => null,
            ])
            ->assertForbidden();
    }

    public function test_publish_requires_description_and_project_date(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();

        DB::table('proker_microsites')->insert([
            'project_id' => $this->projectId('seminar-karier-digital'),
            'is_published' => false,
            'description_md' => null,
            'show_countdown' => true,
            'show_committee' => true,
            'show_gallery' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($owner)
            ->post(route('proker.microsite.publish', ['project' => 'seminar-karier-digital']))
            ->assertSessionHasErrors('microsite');
    }

    public function test_owner_can_publish_and_unpublish_microsite(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();

        DB::table('proker_microsites')->insert([
            'project_id' => $this->projectId('seminar-karier-digital'),
            'is_published' => false,
            'description_md' => 'Deskripsi lengkap untuk halaman publik.',
            'show_countdown' => true,
            'show_committee' => true,
            'show_gallery' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($owner)
            ->post(route('proker.microsite.publish', ['project' => 'seminar-karier-digital']))
            ->assertRedirect()
            ->assertSessionHas('success', 'Microsite proker berhasil dipublish.');

        $this->assertDatabaseHas('proker_microsites', [
            'project_id' => $this->projectId('seminar-karier-digital'),
            'is_published' => true,
        ]);

        $this->actingAs($owner)
            ->post(route('proker.microsite.unpublish', ['project' => 'seminar-karier-digital']))
            ->assertRedirect()
            ->assertSessionHas('success', 'Microsite proker ditarik dari publik.');

        $this->assertDatabaseHas('proker_microsites', [
            'project_id' => $this->projectId('seminar-karier-digital'),
            'is_published' => false,
        ]);
    }

    private function publishMicrosite(string $projectSlug): void
    {
        DB::table('proker_microsites')->insert([
            'project_id' => $this->projectId($projectSlug),
            'is_published' => true,
            'description_md' => 'Event karier digital untuk mahasiswa tingkat akhir.',
            'location_text' => 'Aula Kampus C',
            'location_maps_url' => 'https://maps.example.test/aula',
            'contact_name' => 'Sekretariat BEM',
            'contact_whatsapp' => '+6282112340000',
            'contact_email' => 'event@prokerin.test',
            'show_countdown' => true,
            'show_committee' => true,
            'show_gallery' => true,
            'meta_title' => 'Seminar Karier Digital 2026',
            'meta_description' => 'Daftar dan ikuti seminar karier digital untuk mahasiswa.',
            'published_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function projectId(string $slug): int
    {
        return (int) DB::table('projects')->where('slug', $slug)->value('id');
    }

    private function organizationId(string $slug): int
    {
        return (int) DB::table('organizations')->where('slug', $slug)->value('id');
    }
}
