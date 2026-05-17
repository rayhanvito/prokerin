<?php

declare(strict_types=1);

namespace Tests\Feature\Security;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CertificateRoleAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_disallowed_roles_are_blocked_from_certificate_page(): void
    {
        foreach ([
            'treasurer@prokerin.test',
            'coordinator@prokerin.test',
            'viewer@prokerin.test',
        ] as $email) {
            $this->actingAs($this->user($email))
                ->get(route('certificates.index'))
                ->assertForbidden();
        }
    }

    public function test_allowed_roles_can_open_certificate_page(): void
    {
        foreach ([
            'owner@prokerin.test',
            'admin@prokerin.test',
            'secretary@prokerin.test',
            'lead@prokerin.test',
            'member@prokerin.test',
        ] as $email) {
            $this->actingAs($this->user($email))
                ->get(route('certificates.index'))
                ->assertOk();
        }
    }

    private function user(string $email): User
    {
        return User::query()->where('email', $email)->firstOrFail();
    }
}
