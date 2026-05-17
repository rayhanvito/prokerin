<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

final class AssignTaskPicTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_assignments_page_receives_database_backed_tasks_and_members(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();

        $this->actingAs($owner)
            ->get(route('tasks.assignments'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Task/Assignments')
                ->where('tasks.0.title', 'Finalisasi proposal')
                ->where('tasks.0.picName', 'Salsa Kirana')
                ->has('members', 10));
    }

    public function test_owner_can_assign_task_pic_to_organization_member(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();
        $taskId = $this->taskId('Konfirmasi narasumber');
        $picUserId = $this->userId('koordinator@prokerin.test');

        $this->actingAs($owner)
            ->patch(route('tasks.pic.update', ['task' => $taskId]), [
                'pic_user_id' => $picUserId,
            ])
            ->assertRedirect()
            ->assertSessionHas('success', 'PIC task berhasil diperbarui.');

        $this->assertDatabaseHas('project_tasks', [
            'id' => $taskId,
            'pic_user_id' => $picUserId,
        ]);
    }

    public function test_project_lead_can_assign_task_pic(): void
    {
        $lead = User::query()->where('email', 'lead@prokerin.test')->firstOrFail();
        $taskId = $this->taskId('Konfirmasi narasumber');
        $picUserId = $this->userId('member@prokerin.test');

        $this->actingAs($lead)
            ->patch(route('tasks.pic.update', ['task' => $taskId]), [
                'pic_user_id' => $picUserId,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('project_tasks', [
            'id' => $taskId,
            'pic_user_id' => $picUserId,
        ]);
    }

    public function test_regular_member_cannot_assign_task_pic(): void
    {
        $member = User::query()->where('email', 'member@prokerin.test')->firstOrFail();

        $this->actingAs($member)
            ->patch(route('tasks.pic.update', ['task' => $this->taskId('Konfirmasi narasumber')]), [
                'pic_user_id' => $this->userId('lead@prokerin.test'),
            ])
            ->assertNotFound();
    }

    public function test_pic_must_belong_to_same_organization(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();

        $this->actingAs($owner)
            ->patch(route('tasks.pic.update', ['task' => $this->taskId('Konfirmasi narasumber')]), [
                'pic_user_id' => $this->userId('owner2@prokerin.test'),
            ])
            ->assertNotFound();
    }

    private function taskId(string $title): int
    {
        return (int) DB::table('project_tasks')->where('title', $title)->value('id');
    }

    private function userId(string $email): int
    {
        return (int) DB::table('users')->where('email', $email)->value('id');
    }
}
