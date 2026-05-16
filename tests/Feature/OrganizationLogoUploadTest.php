<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class OrganizationLogoUploadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_organization_owner_can_upload_logo_for_their_active_organization(): void
    {
        Storage::fake('s3');

        $user = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();
        $organizationId = (int) DB::table('organizations')
            ->where('slug', 'bem-fakultas-teknologi')
            ->value('id');

        $response = $this->actingAs($user)->post(route('organization.logo.store'), [
            'logo' => UploadedFile::fake()->create('logo.png', 256, 'image/png'),
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Logo organisasi berhasil diperbarui.');

        Storage::disk('s3')->assertExists("organizations/{$organizationId}/logo.png");
        $this->assertDatabaseHas('organizations', [
            'id' => $organizationId,
            'logo_path' => "organizations/{$organizationId}/logo.png",
        ]);
    }

    public function test_member_cannot_upload_organization_logo(): void
    {
        Storage::fake('s3');

        $user = User::query()->where('email', 'member@prokerin.test')->firstOrFail();

        $this->actingAs($user)
            ->post(route('organization.logo.store'), [
                'logo' => UploadedFile::fake()->create('logo.png', 256, 'image/png'),
            ])
            ->assertForbidden();
    }

    public function test_logo_upload_rejects_unsupported_file_types(): void
    {
        Storage::fake('s3');

        $user = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();

        $this->actingAs($user)
            ->post(route('organization.logo.store'), [
                'logo' => UploadedFile::fake()->create('logo.pdf', 128, 'application/pdf'),
            ])
            ->assertSessionHasErrors('logo');
    }
}
