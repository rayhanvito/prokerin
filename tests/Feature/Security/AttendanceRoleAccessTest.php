<?php

declare(strict_types=1);

namespace Tests\Feature\Security;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AttendanceRoleAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_treasurer_is_blocked_from_attendance_page(): void
    {
        $this->actingAs($this->user('treasurer@prokerin.test'))
            ->get(route('attendance.index'))
            ->assertForbidden();
    }

    public function test_viewer_is_blocked_from_attendance_page(): void
    {
        $this->actingAs($this->user('viewer@prokerin.test'))
            ->get(route('attendance.index'))
            ->assertForbidden();
    }

    public function test_allowed_roles_can_open_attendance_page(): void
    {
        foreach ([
            'owner@prokerin.test',
            'admin@prokerin.test',
            'secretary@prokerin.test',
            'lead@prokerin.test',
            'coordinator@prokerin.test',
            'member@prokerin.test',
        ] as $email) {
            $this->actingAs($this->user($email))
                ->get(route('attendance.index'))
                ->assertOk();
        }
    }

    private function user(string $email): User
    {
        return User::query()->where('email', $email)->firstOrFail();
    }
}
