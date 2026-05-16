<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

final class SponsorVendorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_owner_can_create_sponsor_vendor_contact(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();

        $this->actingAs($owner)
            ->post(route('organization.sponsors-vendors.store'), [
                'type' => 'sponsor',
                'name' => 'Telkom Campus Partner',
                'category' => 'Technology sponsor',
                'contact_person' => 'Rafi Aditya',
                'phone' => '+6281999001111',
                'email' => 'campus@telkom.example',
                'address' => 'Jl. Ketintang, Surabaya',
                'status' => 'active',
                'notes' => 'Prospek untuk workshop teknologi.',
            ])
            ->assertRedirect()
            ->assertSessionHas('success', 'Sponsor/vendor berhasil ditambahkan.');

        $this->assertDatabaseHas('sponsors_vendors', [
            'organization_id' => $this->organizationId('bem-fakultas-teknologi'),
            'type' => 'sponsor',
            'name' => 'Telkom Campus Partner',
            'category' => 'Technology sponsor',
        ]);
    }

    public function test_admin_can_update_sponsor_vendor_contact(): void
    {
        $admin = User::query()->where('email', 'admin@prokerin.test')->firstOrFail();
        $contactId = $this->contactId('CV Audio Visual Nusantara');

        $this->actingAs($admin)
            ->patch(route('organization.sponsors-vendors.update', ['sponsorVendor' => $contactId]), [
                'type' => 'vendor',
                'name' => 'CV Audio Visual Nusantara',
                'category' => 'Production vendor',
                'contact_person' => 'Agus Santoso',
                'phone' => '+6281233004400',
                'email' => 'sales@avn.example',
                'address' => 'Ruko Manyar Indah, Surabaya',
                'status' => 'active',
                'notes' => 'Update kategori vendor produksi.',
            ])
            ->assertRedirect()
            ->assertSessionHas('success', 'Sponsor/vendor berhasil diperbarui.');

        $this->assertDatabaseHas('sponsors_vendors', [
            'id' => $contactId,
            'category' => 'Production vendor',
            'notes' => 'Update kategori vendor produksi.',
        ]);
    }

    public function test_member_cannot_manage_sponsor_vendor_contacts(): void
    {
        $member = User::query()->where('email', 'member@prokerin.test')->firstOrFail();
        $contactId = $this->contactId('CV Audio Visual Nusantara');

        $this->actingAs($member)
            ->post(route('organization.sponsors-vendors.store'), [
                'type' => 'vendor',
                'name' => 'Blocked Vendor',
                'category' => 'Blocked',
                'status' => 'active',
            ])
            ->assertForbidden();

        $this->actingAs($member)
            ->patch(route('organization.sponsors-vendors.update', ['sponsorVendor' => $contactId]), [
                'type' => 'vendor',
                'name' => 'CV Audio Visual Nusantara',
                'category' => 'Blocked',
                'status' => 'active',
            ])
            ->assertForbidden();
    }

    public function test_member_can_view_tenant_scoped_sponsor_vendor_detail_history(): void
    {
        $member = User::query()->where('email', 'member@prokerin.test')->firstOrFail();
        $contactId = $this->contactId('Bank Jatim Youth Program');

        $this->actingAs($member)
            ->get(route('organization.sponsors-vendors.show', ['sponsorVendor' => $contactId]))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Organization/SponsorVendorDetail')
                ->where('contact.name', 'Bank Jatim Youth Program')
                ->has('projects', 1)
                ->where('projects.0.roleDescription', 'Gold sponsor seminar')
                ->has('documents', 1)
                ->where('documents.0.name', 'proposal-v2.pdf'));
    }

    public function test_sponsor_vendor_detail_does_not_leak_across_organizations(): void
    {
        $outsider = User::factory()->create();
        $organizationId = (int) DB::table('organizations')->where('slug', 'hima-informatika')->value('id');
        $contactId = $this->contactId('Bank Jatim Youth Program');

        DB::table('organization_members')->insert([
            'organization_id' => $organizationId,
            'user_id' => $outsider->id,
            'role' => 'viewer',
            'joined_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($outsider)
            ->get(route('organization.sponsors-vendors.show', ['sponsorVendor' => $contactId]))
            ->assertNotFound();
    }

    private function contactId(string $name): int
    {
        return (int) DB::table('sponsors_vendors')->where('name', $name)->value('id');
    }

    private function organizationId(string $slug): int
    {
        return (int) DB::table('organizations')->where('slug', $slug)->value('id');
    }
}
