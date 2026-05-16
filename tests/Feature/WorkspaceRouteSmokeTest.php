<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class WorkspaceRouteSmokeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_authenticated_workspace_routes_render_with_seeded_data(): void
    {
        $user = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();

        foreach ($this->workspaceRouteNames() as $routeName) {
            $this->actingAs($user)
                ->get(route($routeName))
                ->assertOk();
        }
    }

    /**
     * @return array<int, string>
     */
    private function workspaceRouteNames(): array
    {
        return [
            'dashboard',
            'proker.index',
            'proker.create',
            'proker.show',
            'proker.templates',
            'proker.status-flow',
            'organization.setup',
            'organization.switcher',
            'organization.periods',
            'organization.calendar',
            'organization.handover',
            'tasks.index',
            'tasks.kanban',
            'tasks.calendar',
            'tasks.assignments',
            'finance.index',
            'finance.budget-draft',
            'finance.realization',
            'finance.approval',
            'reports.index',
            'reports.proposal-editor',
            'reports.lpj-checklist',
            'reports.export-queue',
            'documents.index',
            'documents.folders',
            'documents.upload-center',
            'members.index',
            'members.invites',
            'members.roles',
            'meetings.index',
            'attendance.index',
            'certificates.index',
            'certificates.templates',
            'certificates.issue',
            'notifications.index',
            'admin.index',
        ];
    }
}
