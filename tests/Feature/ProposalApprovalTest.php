<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Project\ProjectStatus;
use App\Jobs\GenerateDocumentExportJob;
use App\Jobs\SendWhatsAppReminderJob;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class ProposalApprovalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_secretary_can_submit_proposal_for_approval_and_queue_pdf_export(): void
    {
        Queue::fake();

        $secretary = User::query()->where('email', 'sekretaris@prokerin.test')->firstOrFail();
        $draft = DB::table('proposal_drafts')
            ->where('title', 'Proposal Seminar Karier Digital')
            ->first();

        $this->actingAs($secretary)
            ->post(route('reports.proposal-drafts.submit', ['proposalDraft' => $draft->id]))
            ->assertRedirect()
            ->assertSessionHas('success', 'Proposal berhasil dikirim ke approval dan export PDF masuk antrean.');

        $this->assertDatabaseHas('proposal_drafts', [
            'id' => $draft->id,
            'status' => 'submitted',
        ]);

        $this->assertDatabaseHas('projects', [
            'id' => $draft->project_id,
            'status' => ProjectStatus::ProposalReview->value,
        ]);

        $this->assertDatabaseHas('document_exports', [
            'project_id' => $draft->project_id,
            'requested_by_user_id' => $secretary->id,
            'document_title' => 'Proposal Seminar Karier Digital',
            'document_type' => 'proposal',
            'format' => 'pdf',
            'queue_name' => 'exports',
            'engine' => 'browsershot',
            'status' => 'queued',
        ]);

        $documentExportId = (int) DB::table('document_exports')
            ->where('project_id', $draft->project_id)
            ->where('document_title', 'Proposal Seminar Karier Digital')
            ->value('id');

        Queue::assertPushed(
            GenerateDocumentExportJob::class,
            fn (GenerateDocumentExportJob $job): bool => $job->documentExportId === $documentExportId,
        );
        Queue::assertPushed(
            SendWhatsAppReminderJob::class,
            fn (SendWhatsAppReminderJob $job): bool => $job->messageType === 'proposal_review_requested'
                && str_contains($job->message, 'Proposal Seminar Karier Digital'),
        );
    }

    public function test_document_export_job_generates_pdf_and_marks_export_completed(): void
    {
        Storage::fake('s3');

        $exportId = (int) DB::table('document_exports')
            ->where('document_title', 'Proposal Seminar Karier')
            ->value('id');
        $outputPath = (string) DB::table('document_exports')
            ->where('id', $exportId)
            ->value('output_path');

        (new GenerateDocumentExportJob($exportId))->handle();

        Storage::disk('s3')->assertExists($outputPath);
        $this->assertStringStartsWith('%PDF', Storage::disk('s3')->get($outputPath));

        $this->assertDatabaseHas('document_exports', [
            'id' => $exportId,
            'status' => 'completed',
        ]);
    }

    public function test_document_export_job_generates_docx_and_marks_export_completed(): void
    {
        Storage::fake('s3');

        $exportId = (int) DB::table('document_exports')
            ->where('format', 'docx')
            ->value('id');
        $outputPath = (string) DB::table('document_exports')
            ->where('id', $exportId)
            ->value('output_path');

        (new GenerateDocumentExportJob($exportId))->handle();

        Storage::disk('s3')->assertExists($outputPath);
        $this->assertStringStartsWith('PK', Storage::disk('s3')->get($outputPath));

        $this->assertDatabaseHas('document_exports', [
            'id' => $exportId,
            'status' => 'completed',
        ]);
    }

    public function test_secretary_can_update_editable_proposal_sections(): void
    {
        $secretary = User::query()->where('email', 'sekretaris@prokerin.test')->firstOrFail();
        $draft = DB::table('proposal_drafts')
            ->where('title', 'Proposal Seminar Karier Digital')
            ->first();
        $sections = json_decode((string) $draft->sections, true);
        $sections[0]['body'] = 'Latar belakang hasil revisi sekretaris.';

        $this->actingAs($secretary)
            ->patch(route('reports.proposal-drafts.update', ['proposalDraft' => $draft->id]), [
                'sections' => $sections,
            ])
            ->assertRedirect()
            ->assertSessionHas('success', 'Draft proposal berhasil disimpan.');

        $updatedSections = json_decode((string) DB::table('proposal_drafts')
            ->where('id', $draft->id)
            ->value('sections'), true);

        $this->assertSame('Latar belakang hasil revisi sekretaris.', $updatedSections[0]['body']);
        $this->assertDatabaseHas('proposal_drafts', [
            'id' => $draft->id,
            'status' => 'draft',
        ]);
    }

    public function test_revision_requested_proposal_returns_to_draft_when_saved(): void
    {
        $secretary = User::query()->where('email', 'sekretaris@prokerin.test')->firstOrFail();
        $draft = DB::table('proposal_drafts')
            ->where('title', 'Proposal Seminar Karier Digital')
            ->first();
        $sections = json_decode((string) $draft->sections, true);
        $sections[1]['body'] = 'Tujuan kegiatan setelah revisi.';

        DB::table('proposal_drafts')
            ->where('id', $draft->id)
            ->update(['status' => 'revision_requested']);
        DB::table('projects')
            ->where('id', $draft->project_id)
            ->update(['status' => ProjectStatus::Draft->value]);

        $this->actingAs($secretary)
            ->patch(route('reports.proposal-drafts.update', ['proposalDraft' => $draft->id]), [
                'sections' => $sections,
            ])
            ->assertRedirect()
            ->assertSessionHas('success', 'Draft proposal berhasil disimpan.');

        $this->assertDatabaseHas('proposal_drafts', [
            'id' => $draft->id,
            'status' => 'draft',
        ]);
    }

    public function test_submitted_proposal_cannot_be_edited(): void
    {
        $secretary = User::query()->where('email', 'sekretaris@prokerin.test')->firstOrFail();
        $draft = DB::table('proposal_drafts')
            ->where('title', 'Proposal Seminar Karier Digital')
            ->first();
        $sections = json_decode((string) $draft->sections, true);

        DB::table('proposal_drafts')
            ->where('id', $draft->id)
            ->update(['status' => 'submitted']);
        DB::table('projects')
            ->where('id', $draft->project_id)
            ->update(['status' => ProjectStatus::ProposalReview->value]);

        $this->actingAs($secretary)
            ->patch(route('reports.proposal-drafts.update', ['proposalDraft' => $draft->id]), [
                'sections' => $sections,
            ])
            ->assertSessionHasErrors('sections');
    }

    public function test_owner_can_approve_submitted_proposal(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();
        $draft = DB::table('proposal_drafts')
            ->where('title', 'Proposal Seminar Karier Digital')
            ->first();

        DB::table('proposal_drafts')
            ->where('id', $draft->id)
            ->update(['status' => 'submitted']);
        DB::table('projects')
            ->where('id', $draft->project_id)
            ->update(['status' => ProjectStatus::ProposalReview->value]);

        $this->actingAs($owner)
            ->patch(route('reports.proposal-drafts.decision', ['proposalDraft' => $draft->id]), [
                'decision' => 'approve',
            ])
            ->assertRedirect()
            ->assertSessionHas('success', 'Proposal disetujui dan proker masuk tahap RAB approval.');

        $this->assertDatabaseHas('proposal_drafts', [
            'id' => $draft->id,
            'status' => 'approved',
        ]);

        $this->assertDatabaseHas('projects', [
            'id' => $draft->project_id,
            'status' => ProjectStatus::RabApproval->value,
        ]);
    }

    public function test_owner_can_request_proposal_revision(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();
        $draft = DB::table('proposal_drafts')
            ->where('title', 'Proposal Seminar Karier Digital')
            ->first();

        DB::table('proposal_drafts')
            ->where('id', $draft->id)
            ->update(['status' => 'submitted']);
        DB::table('projects')
            ->where('id', $draft->project_id)
            ->update(['status' => ProjectStatus::ProposalReview->value]);

        $this->actingAs($owner)
            ->patch(route('reports.proposal-drafts.decision', ['proposalDraft' => $draft->id]), [
                'decision' => 'request_changes',
            ])
            ->assertRedirect()
            ->assertSessionHas('success', 'Proposal dikembalikan untuk revisi.');

        $this->assertDatabaseHas('proposal_drafts', [
            'id' => $draft->id,
            'status' => 'revision_requested',
        ]);

        $this->assertDatabaseHas('projects', [
            'id' => $draft->project_id,
            'status' => ProjectStatus::Draft->value,
        ]);
    }

    public function test_secretary_cannot_decide_proposal_approval(): void
    {
        $secretary = User::query()->where('email', 'sekretaris@prokerin.test')->firstOrFail();
        $draftId = (int) DB::table('proposal_drafts')->value('id');

        $this->actingAs($secretary)
            ->patch(route('reports.proposal-drafts.decision', ['proposalDraft' => $draftId]), [
                'decision' => 'approve',
            ])
            ->assertForbidden();
    }

    public function test_member_cannot_submit_proposal_for_approval(): void
    {
        $member = User::query()->where('email', 'member@prokerin.test')->firstOrFail();
        $draftId = DB::table('proposal_drafts')->value('id');

        $this->actingAs($member)
            ->post(route('reports.proposal-drafts.submit', ['proposalDraft' => $draftId]))
            ->assertForbidden();
    }
}
