<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

final class QrAttendanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_attendance_page_receives_session_and_record_payload(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();

        $this->actingAs($owner)
            ->get(route('attendance.index'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Attendance/Index')
                ->has('metrics', 3)
                ->where('metrics.0.value', '1')
                ->where('metrics.1.value', '1')
                ->where('metrics.2.value', '1')
                ->has('sessions', 2)
                ->where('sessions.0.title', 'Absensi Technical Meeting Seminar Karier')
                ->where('sessions.0.presentCount', 1)
                ->where('sessions.0.manualCount', 1)
                ->has('recentRecords', 1)
                ->where('recentRecords.0.attendeeName', 'Salsa Kirana'));
    }

    public function test_member_can_check_in_with_valid_qr_token_once(): void
    {
        $treasurer = User::query()->where('email', 'bendahara@prokerin.test')->firstOrFail();

        $this->actingAs($treasurer)
            ->post(route('attendance.check-in.store'), [
                'token' => 'prokerin-m15-technical-meeting-token',
            ])
            ->assertRedirect()
            ->assertSessionHas('success', 'Absensi berhasil dicatat.');

        $this->assertDatabaseHas('attendance_records', [
            'attendance_session_id' => $this->attendanceSessionId('Absensi Technical Meeting Seminar Karier'),
            'user_id' => $treasurer->id,
            'attendee_email' => 'bendahara@prokerin.test',
            'check_in_method' => 'qr',
            'status' => 'present',
        ]);

        $this->assertDatabaseHas('meeting_attendees', [
            'meeting_id' => $this->meetingId('Technical Meeting Seminar Karier'),
            'user_id' => $treasurer->id,
            'attendance_status' => 'present',
        ]);
    }

    public function test_qr_check_in_is_idempotent_for_duplicate_scans(): void
    {
        $treasurer = User::query()->where('email', 'bendahara@prokerin.test')->firstOrFail();

        foreach (range(1, 2) as $attempt) {
            $response = $this->actingAs($treasurer)
                ->post(route('attendance.check-in.store'), [
                    'token' => 'prokerin-m15-technical-meeting-token',
                ])
                ->assertRedirect();

            $attempt === 1
                ? $response->assertSessionHas('success', 'Absensi berhasil dicatat.')
                : $response->assertSessionHas('status', 'Absensi user ini sudah tercatat.');
        }

        $this->assertSame(1, DB::table('attendance_records')
            ->where('attendance_session_id', $this->attendanceSessionId('Absensi Technical Meeting Seminar Karier'))
            ->where('user_id', $treasurer->id)
            ->count());
    }

    public function test_expired_qr_token_is_rejected(): void
    {
        $admin = User::query()->where('email', 'admin@prokerin.test')->firstOrFail();

        $this->actingAs($admin)
            ->post(route('attendance.check-in.store'), [
                'token' => 'prokerin-m15-expired-evaluation-token',
            ])
            ->assertRedirect()
            ->assertSessionHas('error', 'Token QR sudah tidak berlaku.');

        $this->assertDatabaseMissing('attendance_records', [
            'attendance_session_id' => $this->attendanceSessionId('Absensi Evaluasi Proposal dan RAB'),
            'user_id' => $admin->id,
        ]);
    }

    public function test_qr_token_does_not_allow_cross_tenant_check_in(): void
    {
        $outsider = User::query()->where('email', 'test@example.com')->firstOrFail();

        $this->actingAs($outsider)
            ->post(route('attendance.check-in.store'), [
                'token' => 'prokerin-m15-technical-meeting-token',
            ])
            ->assertRedirect()
            ->assertSessionHas('error', 'User tidak terdaftar di organisasi sesi absensi.');
    }

    public function test_admin_can_record_manual_attendance_fallback(): void
    {
        $admin = User::query()->where('email', 'admin@prokerin.test')->firstOrFail();
        $treasurer = User::query()->where('email', 'bendahara@prokerin.test')->firstOrFail();
        $attendeeId = $this->meetingAttendeeId('Technical Meeting Seminar Karier', 'bendahara@prokerin.test');

        $this->actingAs($admin)
            ->post(route('attendance.manual.store', [
                'session' => $this->attendanceSessionId('Absensi Technical Meeting Seminar Karier'),
            ]), [
                'meeting_attendee_id' => $attendeeId,
            ])
            ->assertRedirect()
            ->assertSessionHas('success', 'Absensi manual berhasil dicatat.');

        $this->assertDatabaseHas('attendance_records', [
            'meeting_attendee_id' => $attendeeId,
            'user_id' => $treasurer->id,
            'check_in_method' => 'manual',
            'status' => 'present',
        ]);
    }

    public function test_member_cannot_record_manual_attendance(): void
    {
        $member = User::query()->where('email', 'member@prokerin.test')->firstOrFail();

        $this->actingAs($member)
            ->post(route('attendance.manual.store', [
                'session' => $this->attendanceSessionId('Absensi Technical Meeting Seminar Karier'),
            ]), [
                'meeting_attendee_id' => $this->meetingAttendeeId('Technical Meeting Seminar Karier', 'bendahara@prokerin.test'),
            ])
            ->assertForbidden();
    }

    private function attendanceSessionId(string $title): int
    {
        return (int) DB::table('attendance_sessions')->where('title', $title)->value('id');
    }

    private function meetingId(string $title): int
    {
        return (int) DB::table('meetings')->where('title', $title)->value('id');
    }

    private function meetingAttendeeId(string $meetingTitle, string $email): int
    {
        return (int) DB::table('meeting_attendees')
            ->where('meeting_id', $this->meetingId($meetingTitle))
            ->where('user_id', User::query()->where('email', $email)->value('id'))
            ->value('id');
    }
}
