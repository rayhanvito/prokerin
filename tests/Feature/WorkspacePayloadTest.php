<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
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
            ->has('notificationRules', 7)
            ->where('notificationRules.0.event', 'task_deadline_reminder')
            ->where('notificationRules.0.channels.1', 'email'));
    }

    public function test_sponsor_vendor_page_receives_tenant_scoped_filterable_payload(): void
    {
        $user = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();

        $response = $this->actingAs($user)
            ->get(route('organization.sponsors-vendors', ['type' => 'vendor', 'search' => 'Audio']));

        $response->assertOk();
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Organization/SponsorsVendors')
            ->where('filters.type', 'vendor')
            ->where('filters.search', 'Audio')
            ->where('canManage', true)
            ->where('metrics.total', 3)
            ->has('contacts', 1)
            ->where('contacts.0.name', 'CV Audio Visual Nusantara')
            ->where('contacts.0.linkedProjects', 1));
    }

    public function test_sponsor_vendor_payload_does_not_leak_unlinked_organization_contacts(): void
    {
        $user = User::factory()->create();
        $organizationId = (int) DB::table('organizations')->where('slug', 'hima-informatika')->value('id');

        DB::table('organization_members')->insert([
            'organization_id' => $organizationId,
            'user_id' => $user->id,
            'role' => 'viewer',
            'joined_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($user)
            ->get(route('organization.sponsors-vendors'));

        $response->assertOk();
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Organization/SponsorsVendors')
            ->where('metrics.total', 0)
            ->has('contacts', 0));
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
            ->where('proposalDraft.canEdit', true)
            ->where('proposalDraft.canSubmit', true)
            ->where('proposalDraft.title', 'Proposal Seminar Karier Digital')
            ->has('proposalDraft.sections', 6));
    }

    public function test_lpj_checklist_receives_readiness_payload(): void
    {
        $user = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();

        $response = $this->actingAs($user)
            ->get(route('reports.lpj-checklist'));

        $response->assertOk();
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Reports/LpjChecklist')
            ->where('project.status', 'proposal_review')
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
        $user = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();
        $documentId = (int) DB::table('documents')->where('name', 'documentation-day-1.zip')->value('id');

        $response = $this->actingAs($user)
            ->get(route('documents.upload-center'));

        $response->assertOk();
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Documents/UploadCenter')
            ->has('documents', 3)
            ->where('documents.0.downloadHref', route('documents.download', ['document' => $documentId]))
            ->where('uploadValidation.isValid', true)
            ->where('uploadValidation.requiresSignedUrl', true));
    }
}
