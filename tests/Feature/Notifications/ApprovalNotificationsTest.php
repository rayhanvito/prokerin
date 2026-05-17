<?php

declare(strict_types=1);

namespace Tests\Feature\Notifications;

use App\Models\User;
use App\Notifications\LpjApprovalDecidedNotification;
use App\Notifications\ProposalApprovalDecidedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

final class ApprovalNotificationsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_owner_approving_proposal_notifies_project_lead(): void
    {
        Notification::fake();

        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();
        $projectLead = User::query()->where('email', 'lead@prokerin.test')->firstOrFail();

        $proposalDraft = DB::table('proposal_drafts')
            ->join('projects', 'projects.id', '=', 'proposal_drafts.project_id')
            ->where('projects.slug', 'seminar-karier-digital')
            ->select('proposal_drafts.id')
            ->first();

        // Make sure draft is in submitted state for decide flow
        DB::table('proposal_drafts')
            ->where('id', $proposalDraft->id)
            ->update(['status' => 'submitted']);
        DB::table('projects')
            ->where('slug', 'seminar-karier-digital')
            ->update(['status' => 'proposal_review']);

        $response = $this->actingAs($owner)->patch(
            route('reports.proposal-drafts.decision', ['proposalDraft' => $proposalDraft->id]),
            ['decision' => 'approve'],
        );

        $response->assertRedirect();

        Notification::assertSentTo(
            $projectLead,
            ProposalApprovalDecidedNotification::class,
            static fn (ProposalApprovalDecidedNotification $notification): bool => $notification->decision === 'approved',
        );
    }

    public function test_owner_requesting_lpj_revision_notifies_project_lead(): void
    {
        Notification::fake();

        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();
        $projectLead = User::query()->where('email', 'lead@prokerin.test')->firstOrFail();

        // Move project status into running so LPJ revision is valid
        DB::table('projects')
            ->where('slug', 'seminar-karier-digital')
            ->update(['status' => 'running']);

        $projectId = DB::table('projects')
            ->where('slug', 'seminar-karier-digital')
            ->value('id');

        $response = $this->actingAs($owner)->patch(
            route('reports.lpj.decision', ['project' => $projectId]),
            ['decision' => 'request_changes'],
        );

        $response->assertRedirect();

        Notification::assertSentTo(
            $projectLead,
            LpjApprovalDecidedNotification::class,
            static fn (LpjApprovalDecidedNotification $notification): bool => $notification->decision === 'revision_requested',
        );
    }
}
