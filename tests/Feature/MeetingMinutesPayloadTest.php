<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

final class MeetingMinutesPayloadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_meetings_page_receives_tenant_scoped_meeting_and_minutes_payload(): void
    {
        $user = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();

        $response = $this->actingAs($user)
            ->get(route('meetings.index'));

        $response->assertOk();
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Meetings/Index')
            ->has('metrics', 3)
            ->where('metrics.0.value', '2')
            ->has('meetings', 2)
            ->where('meetings.0.title', 'Evaluasi Proposal dan RAB')
            ->where('meetings.0.presentCount', 3)
            ->where('meetings.0.hasMinutes', true)
            ->has('latestMinutes', 1)
            ->where('latestMinutes.0.meetingTitle', 'Evaluasi Proposal dan RAB')
            ->where('latestMinutes.0.actionItems.0.owner', 'Salsa Kirana'));
    }

    public function test_meetings_payload_does_not_leak_other_organization_meetings(): void
    {
        $user = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();
        $otherOrganizationId = (int) DB::table('organizations')->where('slug', 'hima-informatika')->value('id');
        $otherProjectId = (int) DB::table('projects')->where('slug', 'workshop-ui-ux-hmif')->value('id');

        DB::table('meetings')->insert([
            'organization_id' => $otherOrganizationId,
            'project_id' => $otherProjectId,
            'created_by_user_id' => $user->id,
            'title' => 'Rapat Rahasia HMIF',
            'agenda' => 'Tidak boleh muncul di workspace BEM.',
            'location' => 'Lab HMIF',
            'starts_at' => '2026-05-30 10:00:00',
            'ends_at' => '2026-05-30 11:00:00',
            'status' => 'planned',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($user)
            ->get(route('meetings.index'));

        $response->assertOk();
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Meetings/Index')
            ->has('meetings', 2)
            ->where('metrics.0.value', '2'));
    }
}
