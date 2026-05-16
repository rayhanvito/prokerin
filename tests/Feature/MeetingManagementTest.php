<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Jobs\GenerateDocumentExportJob;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class MeetingManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_secretary_can_create_meeting_with_attendees(): void
    {
        $secretary = User::query()->where('email', 'sekretaris@prokerin.test')->firstOrFail();
        $member = User::query()->where('email', 'member@prokerin.test')->firstOrFail();

        $organizationId = (int) DB::table('organization_members')
            ->where('user_id', $secretary->id)
            ->value('organization_id');
        $projectId = (int) DB::table('projects')
            ->where('organization_id', $organizationId)
            ->value('id');

        $response = $this->actingAs($secretary)->post(route('meetings.store'), [
            'title' => 'Rapat Persiapan Demo',
            'agenda' => 'Pembagian peran demo day',
            'starts_at' => '2026-06-01 09:00:00',
            'ends_at' => '2026-06-01 11:00:00',
            'location' => 'Auditorium A',
            'project_id' => $projectId,
            'attendee_user_ids' => [$secretary->id, $member->id],
        ]);

        $response->assertRedirect(route('meetings.index'));

        $meetingId = (int) DB::table('meetings')
            ->where('title', 'Rapat Persiapan Demo')
            ->value('id');

        $this->assertNotSame(0, $meetingId);
        $this->assertSame(2, (int) DB::table('meeting_attendees')->where('meeting_id', $meetingId)->count());
    }

    public function test_member_cannot_create_meeting(): void
    {
        $member = User::query()->where('email', 'member@prokerin.test')->firstOrFail();

        $response = $this->actingAs($member)->post(route('meetings.store'), [
            'title' => 'Rapat Tidak Boleh',
            'agenda' => 'Test akses',
            'starts_at' => '2026-06-02 09:00:00',
            'attendee_user_ids' => [],
        ]);

        $response->assertForbidden();
        $this->assertSame(0, (int) DB::table('meetings')->where('title', 'Rapat Tidak Boleh')->count());
    }

    public function test_secretary_can_record_attendance(): void
    {
        $secretary = User::query()->where('email', 'sekretaris@prokerin.test')->firstOrFail();

        $meetingId = (int) DB::table('meetings')->where('title', 'Technical Meeting Seminar Karier')->value('id');
        $attendeeId = (int) DB::table('meeting_attendees')
            ->where('meeting_id', $meetingId)
            ->orderBy('id')
            ->value('id');

        $response = $this->actingAs($secretary)->patch(
            route('meetings.attendees.update', ['attendee' => $attendeeId]),
            ['attendance_status' => 'present'],
        );

        $response->assertRedirect();

        $this->assertSame(
            'present',
            (string) DB::table('meeting_attendees')->where('id', $attendeeId)->value('attendance_status'),
        );
    }

    public function test_secretary_can_publish_minutes_and_generate_meeting_payload(): void
    {
        $secretary = User::query()->where('email', 'sekretaris@prokerin.test')->firstOrFail();

        $meetingId = (int) DB::table('meetings')->where('title', 'Technical Meeting Seminar Karier')->value('id');

        $response = $this->actingAs($secretary)->patch(
            route('meetings.minutes.update', ['meeting' => $meetingId]),
            [
                'summary' => 'Final check rundown selesai dan disepakati.',
                'decisions' => ['Approve rundown final', 'Konfirmasi narasumber besok'],
                'action_items' => [
                    [
                        'task' => 'Hubungi narasumber utama',
                        'owner' => 'Lead',
                        'due' => '2026-06-01',
                        'status' => 'open',
                    ],
                ],
                'publish' => true,
            ],
        );

        $response->assertRedirect();

        $minute = DB::table('meeting_minutes')->where('meeting_id', $meetingId)->first();

        $this->assertNotNull($minute);
        $this->assertSame('Final check rundown selesai dan disepakati.', (string) $minute->summary);
        $this->assertNotNull($minute->published_at);
        $this->assertCount(2, json_decode((string) $minute->decisions, true));
        $this->assertCount(1, json_decode((string) $minute->action_items, true));
    }

    public function test_member_cannot_publish_minutes(): void
    {
        $member = User::query()->where('email', 'member@prokerin.test')->firstOrFail();
        $meetingId = (int) DB::table('meetings')->where('title', 'Technical Meeting Seminar Karier')->value('id');

        $response = $this->actingAs($member)->patch(
            route('meetings.minutes.update', ['meeting' => $meetingId]),
            [
                'summary' => 'Bypass attempt',
                'publish' => true,
            ],
        );

        $response->assertForbidden();
    }

    public function test_export_can_only_be_queued_after_minutes_are_published(): void
    {
        Queue::fake();

        $secretary = User::query()->where('email', 'sekretaris@prokerin.test')->firstOrFail();
        $meetingId = (int) DB::table('meetings')->where('title', 'Technical Meeting Seminar Karier')->value('id');

        // First attempt without published minutes should be blocked
        $response = $this->actingAs($secretary)->post(
            route('meetings.exports.store', ['meeting' => $meetingId]),
            ['format' => 'pdf'],
        );

        $response->assertForbidden();
        Queue::assertNothingPushed();

        // Publish minutes
        $this->actingAs($secretary)->patch(
            route('meetings.minutes.update', ['meeting' => $meetingId]),
            [
                'summary' => 'Final.',
                'decisions' => [],
                'action_items' => [],
                'publish' => true,
            ],
        );

        $exportResponse = $this->actingAs($secretary)->post(
            route('meetings.exports.store', ['meeting' => $meetingId]),
            ['format' => 'pdf'],
        );

        $exportResponse->assertRedirect();
        Queue::assertPushed(GenerateDocumentExportJob::class);

        $this->assertSame(1, (int) DB::table('document_exports')
            ->where('document_type', 'meeting_minutes')
            ->count());
    }

    public function test_meetings_payload_renders_form_options_and_attendee_list(): void
    {
        $secretary = User::query()->where('email', 'sekretaris@prokerin.test')->firstOrFail();

        $response = $this->actingAs($secretary)->get(route('meetings.index'));

        $response->assertOk();
        $response->assertInertia(
            fn ($page) => $page
                ->component('Meetings/Index')
                ->where('formOptions.canManage', true)
                ->has('formOptions.organizationMembers.0')
                ->has('formOptions.statusOptions', 4)
                ->has('formOptions.attendanceStatusOptions', 4)
                ->has('meetings.0.attendees.0')
        );
    }

    public function test_meeting_export_content_renders_for_published_minute(): void
    {
        Storage::fake('s3');

        $secretary = User::query()->where('email', 'sekretaris@prokerin.test')->firstOrFail();
        $meetingId = (int) DB::table('meetings')->where('title', 'Technical Meeting Seminar Karier')->value('id');

        $this->actingAs($secretary)->patch(
            route('meetings.minutes.update', ['meeting' => $meetingId]),
            [
                'summary' => 'Sukses.',
                'decisions' => ['Sepakati rundown'],
                'action_items' => [
                    ['task' => 'Booking aula', 'owner' => 'Sekretaris', 'due' => '2026-06-01', 'status' => 'open'],
                ],
                'publish' => true,
            ],
        );

        $this->actingAs($secretary)->post(
            route('meetings.exports.store', ['meeting' => $meetingId]),
            ['format' => 'pdf'],
        );

        $exportId = (int) DB::table('document_exports')
            ->where('document_type', 'meeting_minutes')
            ->orderByDesc('id')
            ->value('id');

        $this->assertNotSame(0, $exportId);

        // Run the queued job synchronously
        (new GenerateDocumentExportJob($exportId))->handle();

        $this->assertSame('completed', (string) DB::table('document_exports')->where('id', $exportId)->value('status'));

        $outputPath = (string) DB::table('document_exports')->where('id', $exportId)->value('output_path');
        Storage::disk('s3')->assertExists($outputPath);
    }
}
