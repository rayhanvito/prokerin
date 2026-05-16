<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class BudgetLineCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_treasurer_can_create_budget_line(): void
    {
        $treasurer = User::query()->where('email', 'bendahara@prokerin.test')->firstOrFail();
        $projectId = (int) DB::table('projects')->where('slug', 'seminar-karier-digital')->value('id');

        $response = $this->actingAs($treasurer)->post(route('finance.budget-lines.store'), [
            'project_id' => $projectId,
            'name' => 'Sewa Sound Tambahan',
            'category' => 'Venue',
            'planned_amount' => 1500000,
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('budget_lines', [
            'project_id' => $projectId,
            'name' => 'Sewa Sound Tambahan',
            'category' => 'Venue',
            'planned_amount' => 1500000,
            'status' => 'draft',
        ]);
    }

    public function test_member_cannot_create_budget_line(): void
    {
        $member = User::query()->where('email', 'member@prokerin.test')->firstOrFail();
        $projectId = (int) DB::table('projects')->where('slug', 'seminar-karier-digital')->value('id');

        $response = $this->actingAs($member)->post(route('finance.budget-lines.store'), [
            'project_id' => $projectId,
            'name' => 'Tambahan',
            'category' => 'Lainnya',
            'planned_amount' => 100000,
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('budget_lines', ['name' => 'Tambahan']);
    }

    public function test_treasurer_cannot_create_budget_line_for_other_organization(): void
    {
        $treasurer = User::query()->where('email', 'bendahara@prokerin.test')->firstOrFail();
        // Project from another org (HIMA Informatika)
        $otherProjectId = (int) DB::table('projects')->where('slug', 'workshop-ui-ux-hmif')->value('id');

        $response = $this->actingAs($treasurer)->post(route('finance.budget-lines.store'), [
            'project_id' => $otherProjectId,
            'name' => 'Bocor',
            'category' => 'Test',
            'planned_amount' => 100000,
        ]);

        $response->assertForbidden();
    }

    public function test_treasurer_can_update_draft_budget_line(): void
    {
        $treasurer = User::query()->where('email', 'bendahara@prokerin.test')->firstOrFail();
        $projectId = (int) DB::table('projects')->where('slug', 'seminar-karier-digital')->value('id');

        $lineId = (int) DB::table('budget_lines')->insertGetId([
            'project_id' => $projectId,
            'name' => 'Original',
            'category' => 'Konsumsi',
            'planned_amount' => 1000000,
            'realized_amount' => 0,
            'status' => 'draft',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($treasurer)->patch(
            route('finance.budget-lines.update', ['budgetLine' => $lineId]),
            [
                'name' => 'Renamed',
                'planned_amount' => 1750000,
            ],
        );

        $response->assertRedirect();
        $this->assertDatabaseHas('budget_lines', [
            'id' => $lineId,
            'name' => 'Renamed',
            'planned_amount' => 1750000,
        ]);
    }

    public function test_cannot_update_approved_budget_line(): void
    {
        $treasurer = User::query()->where('email', 'bendahara@prokerin.test')->firstOrFail();
        $projectId = (int) DB::table('projects')->where('slug', 'seminar-karier-digital')->value('id');

        $lineId = (int) DB::table('budget_lines')->insertGetId([
            'project_id' => $projectId,
            'name' => 'Locked',
            'category' => 'Konsumsi',
            'planned_amount' => 1000000,
            'realized_amount' => 0,
            'status' => 'approved',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($treasurer)->patch(
            route('finance.budget-lines.update', ['budgetLine' => $lineId]),
            ['name' => 'TryRename'],
        );

        $response->assertForbidden();
        $this->assertDatabaseHas('budget_lines', ['id' => $lineId, 'name' => 'Locked']);
    }

    public function test_treasurer_can_delete_draft_budget_line(): void
    {
        $treasurer = User::query()->where('email', 'bendahara@prokerin.test')->firstOrFail();
        $projectId = (int) DB::table('projects')->where('slug', 'seminar-karier-digital')->value('id');

        $lineId = (int) DB::table('budget_lines')->insertGetId([
            'project_id' => $projectId,
            'name' => 'Disposable',
            'category' => 'Lain-lain',
            'planned_amount' => 250000,
            'realized_amount' => 0,
            'status' => 'draft',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($treasurer)->delete(
            route('finance.budget-lines.destroy', ['budgetLine' => $lineId]),
        );

        $response->assertRedirect();
        $this->assertDatabaseMissing('budget_lines', ['id' => $lineId]);
    }

    public function test_cannot_delete_budget_line_with_transactions(): void
    {
        $treasurer = User::query()->where('email', 'bendahara@prokerin.test')->firstOrFail();
        $lineId = (int) DB::table('budget_lines')
            ->where('name', 'Konsumsi peserta')
            ->value('id');

        // The seed includes a budget transaction for this line.
        $response = $this->actingAs($treasurer)->delete(
            route('finance.budget-lines.destroy', ['budgetLine' => $lineId]),
        );

        // Status realized + has transactions: should be forbidden
        $response->assertForbidden();
        $this->assertDatabaseHas('budget_lines', ['id' => $lineId]);
    }

    public function test_budget_draft_payload_returns_lines_and_summary(): void
    {
        $treasurer = User::query()->where('email', 'bendahara@prokerin.test')->firstOrFail();

        $response = $this->actingAs($treasurer)->get(route('finance.budget-draft'));

        $response->assertOk();
        $response->assertInertia(
            fn ($page) => $page
                ->component('Finance/BudgetDraft')
                ->where('canManage', true)
                ->has('summary')
                ->has('lines.0')
                ->has('projects.0')
        );
    }

    public function test_budget_draft_payload_does_not_leak_other_org_lines(): void
    {
        // Owner BEM has only seminar-karier-digital lines.
        $treasurer = User::query()->where('email', 'bendahara@prokerin.test')->firstOrFail();

        $response = $this->actingAs($treasurer)->get(route('finance.budget-draft'));

        $response->assertOk();
        $response->assertInertia(
            fn ($page) => $page->where(
                'lines',
                fn ($lines) => collect($lines)
                    ->every(fn ($line) => $line['projectName'] === 'Seminar Karier Digital'),
            )
        );
    }
}
