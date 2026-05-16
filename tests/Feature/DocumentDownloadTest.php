<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class DocumentDownloadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        Storage::fake('s3');
    }

    public function test_secretary_gets_signed_url_for_private_proposal_document(): void
    {
        $secretary = User::query()->where('email', 'sekretaris@prokerin.test')->firstOrFail();

        $response = $this->actingAs($secretary)
            ->get(route('documents.download', ['document' => $this->documentId('proposal-v2.pdf')]));

        $response->assertRedirect();
        $location = (string) $response->headers->get('Location');

        $this->assertStringContainsString('proposal-v2.pdf', $location);
        $this->assertStringContainsString('expiration=', $location);
    }

    public function test_treasurer_gets_signed_url_for_restricted_receipt_document(): void
    {
        $treasurer = User::query()->where('email', 'bendahara@prokerin.test')->firstOrFail();

        $response = $this->actingAs($treasurer)
            ->get(route('documents.download', ['document' => $this->documentId('receipt-consumption.jpg')]));

        $response->assertRedirect();
        $location = (string) $response->headers->get('Location');

        $this->assertStringContainsString('receipt-consumption.jpg', $location);
        $this->assertStringContainsString('expiration=', $location);
    }

    public function test_committee_document_download_uses_public_storage_url_for_workspace_member(): void
    {
        $member = User::query()->where('email', 'member@prokerin.test')->firstOrFail();

        $response = $this->actingAs($member)
            ->get(route('documents.download', ['document' => $this->documentId('documentation-day-1.zip')]));

        $response->assertRedirect();
        $location = (string) $response->headers->get('Location');

        $this->assertStringContainsString('documentation-day-1.zip', $location);
        $this->assertStringNotContainsString('expiration=', $location);
    }

    public function test_member_cannot_download_restricted_finance_receipt(): void
    {
        $member = User::query()->where('email', 'member@prokerin.test')->firstOrFail();

        $this->actingAs($member)
            ->get(route('documents.download', ['document' => $this->documentId('receipt-consumption.jpg')]))
            ->assertForbidden();
    }

    public function test_other_organization_owner_cannot_download_workspace_document(): void
    {
        $otherOwner = User::query()->where('email', 'owner2@prokerin.test')->firstOrFail();

        $this->actingAs($otherOwner)
            ->get(route('documents.download', ['document' => $this->documentId('proposal-v2.pdf')]))
            ->assertNotFound();
    }

    public function test_workspace_member_gets_signed_url_for_completed_document_export(): void
    {
        $secretary = User::query()->where('email', 'sekretaris@prokerin.test')->firstOrFail();
        $exportId = $this->completedExportId();

        $response = $this->actingAs($secretary)
            ->get(route('reports.exports.download', ['documentExport' => $exportId]));

        $response->assertRedirect();
        $location = (string) $response->headers->get('Location');

        $this->assertStringContainsString('proposal-seminar-karier.pdf', $location);
        $this->assertStringContainsString('expiration=', $location);
    }

    public function test_document_export_download_waits_until_export_is_completed(): void
    {
        $secretary = User::query()->where('email', 'sekretaris@prokerin.test')->firstOrFail();
        $exportId = (int) DB::table('document_exports')->where('status', 'queued')->value('id');

        $this->actingAs($secretary)
            ->get(route('reports.exports.download', ['documentExport' => $exportId]))
            ->assertConflict();
    }

    public function test_non_workspace_user_cannot_download_document_export(): void
    {
        $outsider = User::factory()->create([
            'email' => 'outsider@prokerin.test',
        ]);

        $this->actingAs($outsider)
            ->get(route('reports.exports.download', ['documentExport' => $this->completedExportId()]))
            ->assertNotFound();
    }

    private function documentId(string $name): int
    {
        return (int) DB::table('documents')->where('name', $name)->value('id');
    }

    private function completedExportId(): int
    {
        $exportId = (int) DB::table('document_exports')
            ->where('document_title', 'Proposal Seminar Karier')
            ->value('id');

        DB::table('document_exports')
            ->where('id', $exportId)
            ->update(['status' => 'completed']);

        return $exportId;
    }
}
