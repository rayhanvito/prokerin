<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Project\ProjectStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
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
