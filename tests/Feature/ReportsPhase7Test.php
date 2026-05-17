<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Domain\Project\ProjectStatus;
use App\Jobs\GenerateDocumentExportJob;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

final class ReportsPhase7Test extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_reports_overview_is_database_backed(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();

        $this->actingAs($owner)
            ->get(route('reports.index'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Reports/Index')
                ->where('metrics.0.value', '1')
                ->where('metrics.2.value', '2')
                ->where('proposalStatuses.0.status', 'draft')
                ->has('exportQueue', 2)
                ->has('recentProjects', 1));
    }

    public function test_lpj_payload_includes_item_ids_and_execution_summary(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();

        $this->actingAs($owner)
            ->get(route('reports.lpj-checklist'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Reports/LpjChecklist')
                ->where('checklistItems.0.id', $this->firstChecklistItemId())
                ->where('executionSummary.completedTasks', 1)
                ->where('executionSummary.totalTasks', 4)
                ->where('executionSummary.realizedBudget', 2650000)
                ->where('executionSummary.attendanceCount', 1));
    }

    public function test_secretary_can_toggle_lpj_checklist_item(): void
    {
        $secretary = User::query()->where('email', 'sekretaris@prokerin.test')->firstOrFail();
        $itemId = $this->firstIncompleteChecklistItemId();

        DB::table('projects')
            ->where('id', $this->projectId())
            ->update(['status' => ProjectStatus::Running->value]);

        $this->actingAs($secretary)
            ->patch(route('reports.lpj-checklist.items.update', ['item' => $itemId]), [
                'is_complete' => true,
            ])
            ->assertRedirect()
            ->assertSessionHas('success', 'Checklist LPJ berhasil diperbarui.');

        $this->assertDatabaseHas('lpj_checklist_items', [
            'id' => $itemId,
            'is_complete' => true,
        ]);
    }

    public function test_member_cannot_toggle_lpj_checklist_item(): void
    {
        $member = User::query()->where('email', 'member@prokerin.test')->firstOrFail();
        $itemId = $this->firstIncompleteChecklistItemId();

        $this->actingAs($member)
            ->patch(route('reports.lpj-checklist.items.update', ['item' => $itemId]), [
                'is_complete' => true,
            ])
            ->assertNotFound();
    }

    public function test_owner_can_queue_lpj_pdf_export_after_completion(): void
    {
        Queue::fake();

        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();
        $projectId = $this->projectId();

        DB::table('projects')
            ->where('id', $projectId)
            ->update(['status' => ProjectStatus::Completed->value]);

        $this->actingAs($owner)
            ->post(route('reports.lpj-checklist.export', ['project' => $projectId]))
            ->assertRedirect()
            ->assertSessionHas('success', 'Export LPJ PDF berhasil di-queue.');

        $documentExportId = (int) DB::table('document_exports')
            ->where('document_title', 'LPJ Seminar Karier Digital')
            ->where('format', 'pdf')
            ->value('id');

        $this->assertGreaterThan(0, $documentExportId);
        Queue::assertPushed(
            GenerateDocumentExportJob::class,
            fn (GenerateDocumentExportJob $job): bool => $job->documentExportId === $documentExportId,
        );
    }

    public function test_lpj_pdf_export_requires_completed_project(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();

        $this->actingAs($owner)
            ->post(route('reports.lpj-checklist.export', ['project' => $this->projectId()]))
            ->assertForbidden();
    }

    private function firstChecklistItemId(): int
    {
        return (int) DB::table('lpj_checklist_items')
            ->where('project_id', $this->projectId())
            ->orderBy('id')
            ->value('id');
    }

    private function firstIncompleteChecklistItemId(): int
    {
        return (int) DB::table('lpj_checklist_items')
            ->where('project_id', $this->projectId())
            ->where('is_complete', false)
            ->orderBy('id')
            ->value('id');
    }

    private function projectId(): int
    {
        return (int) DB::table('projects')
            ->where('slug', 'seminar-karier-digital')
            ->value('id');
    }
}
