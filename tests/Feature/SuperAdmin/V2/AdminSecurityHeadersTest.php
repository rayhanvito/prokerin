<?php

declare(strict_types=1);

namespace Tests\Feature\SuperAdmin\V2;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AdminSecurityHeadersTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_internal_admin_responses_include_noindex_headers(): void
    {
        $superAdmin = User::query()->where('email', 'superadmin@prokerin.internal')->firstOrFail();

        $this->actingAs($superAdmin)
            ->get('/internal-admin')
            ->assertOk()
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow')
            ->assertHeader('X-Frame-Options', 'DENY');
    }

    public function test_internal_admin_ip_allowlist_blocks_unknown_ip_when_configured(): void
    {
        config(['admin.allowed_ips' => ['203.0.113.10']]);

        $superAdmin = User::query()->where('email', 'superadmin@prokerin.internal')->firstOrFail();

        $this->actingAs($superAdmin)
            ->withServerVariables(['REMOTE_ADDR' => '198.51.100.20'])
            ->get('/internal-admin')
            ->assertForbidden();
    }

    public function test_internal_admin_idle_session_expires(): void
    {
        config(['admin.session_idle_minutes' => 30]);

        $superAdmin = User::query()->where('email', 'superadmin@prokerin.internal')->firstOrFail();

        $this->actingAs($superAdmin)
            ->withSession(['admin_last_activity_at' => now()->subMinutes(31)->toIso8601String()])
            ->get('/internal-admin')
            ->assertRedirect('/internal-admin/login');
    }
}
