<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class QrCameraCheckInTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_member_valid_camera_scan_records_qr_camera_method(): void
    {
        $member = User::query()->where('email', 'member@prokerin.test')->firstOrFail();

        $this->actingAs($member)
            ->post(route('attendance.check-in.store'), [
                'token' => 'prokerin-m15-technical-meeting-token',
                'method' => 'qr_camera',
            ])
            ->assertRedirect()
            ->assertSessionHas('success', 'Absensi berhasil dicatat.');

        $this->assertDatabaseHas('attendance_records', [
            'attendance_session_id' => $this->attendanceSessionId('Absensi Technical Meeting Seminar Karier'),
            'user_id' => $member->id,
            'attendee_email' => 'member@prokerin.test',
            'check_in_method' => 'qr_camera',
            'status' => 'present',
        ]);
    }

    public function test_camera_scan_rejects_expired_token(): void
    {
        $member = User::query()->where('email', 'member@prokerin.test')->firstOrFail();

        $this->actingAs($member)
            ->post(route('attendance.check-in.store'), [
                'token' => 'prokerin-m15-expired-evaluation-token',
                'method' => 'qr_camera',
            ])
            ->assertRedirect()
            ->assertSessionHas('error', 'Token QR sudah tidak berlaku.');
    }

    public function test_camera_scan_rejects_cross_tenant_user(): void
    {
        $outsider = User::query()->where('email', 'test@example.com')->firstOrFail();

        $this->actingAs($outsider)
            ->post(route('attendance.check-in.store'), [
                'token' => 'prokerin-m15-technical-meeting-token',
                'method' => 'qr_camera',
            ])
            ->assertRedirect()
            ->assertSessionHas('error', 'User tidak terdaftar di organisasi sesi absensi.');
    }

    public function test_manual_paste_keeps_original_qr_method(): void
    {
        $member = User::query()->where('email', 'member@prokerin.test')->firstOrFail();

        $this->actingAs($member)
            ->post(route('attendance.check-in.store'), [
                'token' => 'prokerin-m15-technical-meeting-token',
            ])
            ->assertRedirect()
            ->assertSessionHas('success', 'Absensi berhasil dicatat.');

        $this->assertDatabaseHas('attendance_records', [
            'attendance_session_id' => $this->attendanceSessionId('Absensi Technical Meeting Seminar Karier'),
            'user_id' => $member->id,
            'check_in_method' => 'qr',
        ]);
    }

    private function attendanceSessionId(string $title): int
    {
        return (int) DB::table('attendance_sessions')->where('title', $title)->value('id');
    }
}
