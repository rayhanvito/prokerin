<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class AttendanceQrManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_secretary_can_issue_qr_token_and_revoke_old_ones(): void
    {
        $secretary = User::query()->where('email', 'sekretaris@prokerin.test')->firstOrFail();
        $sessionId = (int) DB::table('attendance_sessions')->orderBy('id')->value('id');

        $previousActive = (int) DB::table('attendance_qr_tokens')
            ->where('attendance_session_id', $sessionId)
            ->whereNull('revoked_at')
            ->count();

        $response = $this->actingAs($secretary)->post(
            route('attendance.qr-tokens.store', ['session' => $sessionId]),
        );

        $response->assertRedirect();
        $this->assertSame(
            1,
            (int) DB::table('attendance_qr_tokens')
                ->where('attendance_session_id', $sessionId)
                ->whereNull('revoked_at')
                ->count(),
            'Hanya satu token aktif tersisa setelah issue baru',
        );
        $this->assertGreaterThanOrEqual(
            $previousActive,
            (int) DB::table('attendance_qr_tokens')->where('attendance_session_id', $sessionId)->count(),
        );

        $session = $response->getSession();
        $token = $session->get('attendanceQrToken');
        $this->assertIsArray($token);
        $this->assertArrayHasKey('plainToken', $token);
        $this->assertSame($sessionId, $token['sessionId']);
    }

    public function test_member_cannot_issue_qr_token(): void
    {
        $member = User::query()->where('email', 'member@prokerin.test')->firstOrFail();
        $sessionId = (int) DB::table('attendance_sessions')->orderBy('id')->value('id');

        $response = $this->actingAs($member)->post(
            route('attendance.qr-tokens.store', ['session' => $sessionId]),
        );

        $response->assertForbidden();
    }

    public function test_secretary_can_revoke_active_token(): void
    {
        $secretary = User::query()->where('email', 'sekretaris@prokerin.test')->firstOrFail();
        $sessionId = (int) DB::table('attendance_sessions')->orderBy('id')->value('id');

        $this->actingAs($secretary)->post(
            route('attendance.qr-tokens.store', ['session' => $sessionId]),
        );

        $tokenId = (int) DB::table('attendance_qr_tokens')
            ->where('attendance_session_id', $sessionId)
            ->whereNull('revoked_at')
            ->orderByDesc('id')
            ->value('id');

        $this->assertNotSame(0, $tokenId);

        $response = $this->actingAs($secretary)->delete(
            route('attendance.qr-tokens.destroy', ['token' => $tokenId]),
        );

        $response->assertRedirect();
        $this->assertNotNull(
            DB::table('attendance_qr_tokens')->where('id', $tokenId)->value('revoked_at'),
        );
    }

    public function test_qr_image_endpoint_returns_svg(): void
    {
        $secretary = User::query()->where('email', 'sekretaris@prokerin.test')->firstOrFail();

        $response = $this->actingAs($secretary)->get(
            route('attendance.qr-image.show', ['token' => 'test-token-abc']),
        );

        $response->assertOk();
        $this->assertSame('image/svg+xml', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('<svg', $response->getContent());
    }

    public function test_qr_image_endpoint_rejects_empty_token(): void
    {
        $secretary = User::query()->where('email', 'sekretaris@prokerin.test')->firstOrFail();

        $response = $this->actingAs($secretary)->get(route('attendance.qr-image.show'));

        $response->assertStatus(422);
    }

    public function test_attendance_csv_export_returns_csv_for_authorized_user(): void
    {
        $secretary = User::query()->where('email', 'sekretaris@prokerin.test')->firstOrFail();
        $sessionId = (int) DB::table('attendance_sessions')->orderBy('id')->value('id');

        $response = $this->actingAs($secretary)->get(
            route('attendance.export.csv', ['session' => $sessionId]),
        );

        $response->assertOk();
        $this->assertStringContainsString('text/csv', (string) $response->headers->get('Content-Type'));
        $this->assertStringContainsString('name,email,method,checked_in_at,status,notes', $response->getContent());
    }

    public function test_attendance_csv_export_blocks_non_member(): void
    {
        // Create a user not part of the seeded organizations
        $outsider = User::factory()->create(['email' => 'outsider@prokerin.test']);
        $sessionId = (int) DB::table('attendance_sessions')->orderBy('id')->value('id');

        $response = $this->actingAs($outsider)->get(
            route('attendance.export.csv', ['session' => $sessionId]),
        );

        $response->assertForbidden();
    }

    public function test_attendance_payload_exposes_active_token_and_can_manage_flag(): void
    {
        $secretary = User::query()->where('email', 'sekretaris@prokerin.test')->firstOrFail();

        $response = $this->actingAs($secretary)->get(route('attendance.index'));

        $response->assertOk();
        $response->assertInertia(
            fn ($page) => $page
                ->component('Attendance/Index')
                ->where('sessions.0.canManageQr', true)
                ->has('sessions.0.activeTokenId')
        );
    }
}
