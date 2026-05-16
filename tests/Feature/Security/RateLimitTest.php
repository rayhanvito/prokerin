<?php

declare(strict_types=1);

namespace Tests\Feature\Security;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

final class RateLimitTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
        RateLimiter::clear('login:wronguser@prokerin.test|127.0.0.1');
        RateLimiter::clear('password.email:127.0.0.1');
    }

    public function test_login_route_throttles_after_five_failed_attempts(): void
    {
        // Six attempts: first 5 should fail with 422 validation; 6th should be 429.
        for ($i = 0; $i < 5; $i++) {
            $this->post('/login', [
                'email' => 'wronguser@prokerin.test',
                'password' => 'wrong-password',
            ])->assertStatus(302); // login fail redirects with errors
        }

        $response = $this->post('/login', [
            'email' => 'wronguser@prokerin.test',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(429);
    }

    public function test_forgot_password_route_throttles_after_three_attempts(): void
    {
        for ($i = 0; $i < 3; $i++) {
            $this->post('/forgot-password', [
                'email' => 'someone@prokerin.test',
            ]);
        }

        $response = $this->post('/forgot-password', [
            'email' => 'someone@prokerin.test',
        ]);

        $response->assertStatus(429);
    }

    public function test_certificate_verify_endpoint_throttles_brute_force_attempts(): void
    {
        for ($i = 0; $i < 20; $i++) {
            $this->get('/verify/random-token-'.$i);
        }

        $response = $this->get('/verify/random-token-spam');

        $response->assertStatus(429);
    }

    public function test_invitation_dispatch_throttles_per_organization(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();

        // Build 21 attempts; first 20 within the limit, 21st must be 429.
        $rateLimited = false;
        $this->actingAs($owner);

        for ($i = 0; $i < 21; $i++) {
            $response = $this->post(route('organization.invitations.store'), [
                'email' => "invitee{$i}@prokerin.test",
                'role' => 'member',
            ]);

            if ($response->getStatusCode() === 429) {
                $rateLimited = true;
                break;
            }
        }

        $this->assertTrue($rateLimited, 'Invitation dispatch did not enforce rate limit after 20 attempts.');
    }
}
