<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Document\DocumentVisibility;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

final class DocumentUploadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        Storage::fake('s3');
    }

    public function test_owner_can_upload_valid_pdf_document(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();
        $file = UploadedFile::fake()->create('proposal-final.pdf', 256, 'application/pdf');

        $this->actingAs($owner)
            ->post(route('documents.store'), [
                'file' => $file,
                'folder' => 'Proposal',
                'visibility' => DocumentVisibility::Private->value,
                'project_id' => $this->projectId('seminar-karier-digital'),
            ])
            ->assertRedirect()
            ->assertSessionHas('success', 'Dokumen berhasil diunggah.');

        $document = DB::table('documents')
            ->where('name', 'proposal-final.pdf')
            ->first();

        $this->assertNotNull($document);
        $this->assertSame(DocumentVisibility::Private->value, $document->visibility);
        Storage::disk('s3')->assertExists((string) $document->storage_path);
    }

    public function test_upload_rejects_php_executable_and_svg_payload(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();

        $this->actingAs($owner)
            ->post(route('documents.store'), [
                'file' => UploadedFile::fake()->create('shell.php', 1, 'application/x-php'),
                'folder' => 'Security',
                'visibility' => DocumentVisibility::Private->value,
            ])
            ->assertSessionHasErrors('file');

        $this->actingAs($owner)
            ->post(route('documents.store'), [
                'file' => UploadedFile::fake()->createWithContent(
                    'payload.svg',
                    '<svg><script>alert("xss")</script></svg>',
                ),
                'folder' => 'Security',
                'visibility' => DocumentVisibility::Public->value,
            ])
            ->assertSessionHasErrors('file');
    }

    public function test_upload_rejects_files_larger_than_ten_megabytes(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();

        $this->actingAs($owner)
            ->post(route('documents.store'), [
                'file' => UploadedFile::fake()->create('oversized.pdf', 10241, 'application/pdf'),
                'folder' => 'Proposal',
                'visibility' => DocumentVisibility::Private->value,
            ])
            ->assertSessionHasErrors('file');
    }

    public function test_cross_tenant_project_upload_is_forbidden(): void
    {
        $otherOwner = User::query()->where('email', 'owner2@prokerin.test')->firstOrFail();

        $this->actingAs($otherOwner)
            ->withSession(['active_organization_id' => $this->organizationId('ukm-kreatif')])
            ->post(route('documents.store'), [
                'file' => UploadedFile::fake()->create('proposal-final.pdf', 256, 'application/pdf'),
                'folder' => 'Proposal',
                'visibility' => DocumentVisibility::Private->value,
                'project_id' => $this->projectId('seminar-karier-digital'),
            ])
            ->assertForbidden();
    }

    public function test_document_folders_are_database_backed(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();

        $this->actingAs($owner)
            ->get(route('documents.folders'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Documents/Folders')
                ->where('folders.0.name', 'Documentation')
                ->where('folders.0.files', 1)
                ->where('folders.0.documents.0.name', 'documentation-day-1.zip'));
    }

    public function test_document_folders_respect_visibility_rules(): void
    {
        $member = User::query()->where('email', 'member@prokerin.test')->firstOrFail();

        $this->actingAs($member)
            ->get(route('documents.folders'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Documents/Folders')
                ->has('folders', 1)
                ->where('folders.0.name', 'Documentation')
                ->where('folders.0.documents.0.name', 'documentation-day-1.zip'));
    }

    public function test_private_and_public_visibility_rules_are_enforced(): void
    {
        $member = User::query()->where('email', 'member@prokerin.test')->firstOrFail();
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();
        $publicDocumentId = $this->insertDocument(
            ownerUserId: (int) $owner->id,
            visibility: DocumentVisibility::Public,
            name: 'public-guide.pdf',
        );

        $this->actingAs($member)
            ->get(route('documents.download', ['document' => $this->documentId('proposal-v2.pdf')]))
            ->assertForbidden();

        $this->actingAs($member)
            ->get(route('documents.download', ['document' => $publicDocumentId]))
            ->assertRedirect();
    }

    public function test_committee_visibility_requires_project_membership_or_secretary_access(): void
    {
        $treasurer = User::query()->where('email', 'bendahara@prokerin.test')->firstOrFail();
        $member = User::query()->where('email', 'member@prokerin.test')->firstOrFail();
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();
        $documentId = $this->insertDocument(
            ownerUserId: (int) $owner->id,
            visibility: DocumentVisibility::Committee,
            name: 'committee-only.pdf',
            projectId: $this->projectId('seminar-karier-digital'),
        );

        $this->actingAs($treasurer)
            ->get(route('documents.download', ['document' => $documentId]))
            ->assertForbidden();

        $this->actingAs($member)
            ->get(route('documents.download', ['document' => $documentId]))
            ->assertRedirect();
    }

    private function insertDocument(
        int $ownerUserId,
        DocumentVisibility $visibility,
        string $name,
        ?int $projectId = null,
    ): int {
        Storage::disk('s3')->put('documents/tests/'.$name, 'document');

        return (int) DB::table('documents')->insertGetId([
            'organization_id' => $this->organizationId('bem-fakultas-teknologi'),
            'project_id' => $projectId,
            'owner_user_id' => $ownerUserId,
            'name' => $name,
            'folder' => 'QA',
            'storage_path' => 'documents/tests/'.$name,
            'mime_type' => 'application/pdf',
            'size_kb' => 1,
            'visibility' => $visibility->value,
            'status' => 'uploaded',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function documentId(string $name): int
    {
        return (int) DB::table('documents')->where('name', $name)->value('id');
    }

    private function organizationId(string $slug): int
    {
        return (int) DB::table('organizations')->where('slug', $slug)->value('id');
    }

    private function projectId(string $slug): int
    {
        return (int) DB::table('projects')->where('slug', $slug)->value('id');
    }
}
