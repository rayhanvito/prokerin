<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

final class WorkspacePayloadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_role_matrix_page_receives_role_permission_payload(): void
    {
        $response = $this->actingAs(User::factory()->create())
            ->get(route('members.roles'));

        $response->assertOk();
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Members/Roles')
            ->has('rolePermissions', 10)
            ->where('rolePermissions.0.role', 'organization_owner')
            ->where('rolePermissions.0.isSystemRole', true));
    }

    public function test_notification_page_receives_default_rule_payload(): void
    {
        $response = $this->actingAs(User::factory()->create())
            ->get(route('notifications.index'));

        $response->assertOk();
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Notifications/Index')
            ->has('notificationRules', 5)
            ->where('notificationRules.0.event', 'task_deadline_reminder')
            ->where('notificationRules.0.channels.1', 'email'));
    }

    public function test_template_page_receives_template_plan_payload(): void
    {
        $response = $this->actingAs(User::factory()->create())
            ->get(route('proker.templates'));

        $response->assertOk();
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Proker/Templates')
            ->has('templates', 4)
            ->where('templates.0.type', 'seminar')
            ->has('templates.0.plan.tasks')
            ->has('templates.0.plan.budgetLines'));
    }

    public function test_proposal_editor_receives_draft_payload(): void
    {
        $user = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();

        $response = $this->actingAs($user)
            ->get(route('reports.proposal-editor'));

        $response->assertOk();
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Reports/ProposalEditor')
            ->where('proposalDraft.status', 'draft')
            ->where('proposalDraft.canSubmit', true)
            ->where('proposalDraft.title', 'Proposal Seminar Karier Digital')
            ->has('proposalDraft.sections', 6));
    }

    public function test_lpj_checklist_receives_readiness_payload(): void
    {
        $response = $this->actingAs(User::factory()->create())
            ->get(route('reports.lpj-checklist'));

        $response->assertOk();
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Reports/LpjChecklist')
            ->has('checklistItems', 5)
            ->where('readiness.completionProgress', 40)
            ->where('readiness.isReadyForReview', false));
    }

    public function test_export_queue_receives_export_plan_payload(): void
    {
        $user = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();

        $response = $this->actingAs($user)
            ->get(route('reports.export-queue'));

        $response->assertOk();
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Reports/ExportQueue')
            ->has('exportQueue', 2)
            ->where('exportQueue.0.plan.queueName', 'exports')
            ->where('exportQueue.0.plan.engine', 'browsershot'));
    }

    public function test_upload_center_receives_upload_validation_payload(): void
    {
        $response = $this->actingAs(User::factory()->create())
            ->get(route('documents.upload-center'));

        $response->assertOk();
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Documents/UploadCenter')
            ->where('uploadValidation.isValid', true)
            ->where('uploadValidation.requiresSignedUrl', true));
    }
}
