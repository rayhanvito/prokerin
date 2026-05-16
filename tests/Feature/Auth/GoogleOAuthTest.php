<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

final class GoogleOAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_google_redirect_sends_guest_to_google_authorization_url(): void
    {
        config()->set('services.google', [
            'client_id' => 'google-client-id',
            'client_secret' => 'google-client-secret',
            'redirect' => 'http://localhost/auth/google/callback',
        ]);

        $response = $this->get(route('auth.google.redirect'));

        $response->assertRedirectContains('https://accounts.google.com/o/oauth2/v2/auth');
        $response->assertSessionHas('auth.google_oauth_state');
    }

    public function test_google_callback_creates_and_authenticates_user(): void
    {
        config()->set('services.google', [
            'client_id' => 'google-client-id',
            'client_secret' => 'google-client-secret',
            'redirect' => 'http://localhost/auth/google/callback',
        ]);

        Http::fake([
            'https://oauth2.googleapis.com/token' => Http::response([
                'access_token' => 'google-access-token',
            ]),
            'https://www.googleapis.com/oauth2/v3/userinfo' => Http::response([
                'sub' => 'google-user-123',
                'name' => 'Google User',
                'email' => 'GOOGLE.USER@kampus.test',
                'email_verified' => true,
                'picture' => 'https://example.com/avatar.png',
            ]),
        ]);

        $response = $this
            ->withSession(['auth.google_oauth_state' => 'expected-state'])
            ->get(route('auth.google.callback', [
                'state' => 'expected-state',
                'code' => 'valid-code',
            ]));

        $response->assertRedirect(route('dashboard', absolute: false));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'google.user@kampus.test',
            'google_id' => 'google-user-123',
            'avatar_url' => 'https://example.com/avatar.png',
        ]);
    }

    public function test_google_callback_links_existing_user_by_email(): void
    {
        $user = User::factory()->create([
            'email' => 'existing@kampus.test',
            'google_id' => null,
        ]);

        config()->set('services.google', [
            'client_id' => 'google-client-id',
            'client_secret' => 'google-client-secret',
            'redirect' => 'http://localhost/auth/google/callback',
        ]);

        Http::fake([
            'https://oauth2.googleapis.com/token' => Http::response([
                'access_token' => 'google-access-token',
            ]),
            'https://www.googleapis.com/oauth2/v3/userinfo' => Http::response([
                'sub' => 'google-existing-123',
                'name' => 'Existing User',
                'email' => 'existing@kampus.test',
                'email_verified' => true,
            ]),
        ]);

        $this
            ->withSession(['auth.google_oauth_state' => 'expected-state'])
            ->get(route('auth.google.callback', [
                'state' => 'expected-state',
                'code' => 'valid-code',
            ]))
            ->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticatedAs($user->refresh());
        $this->assertSame('google-existing-123', $user->google_id);
    }

    public function test_google_callback_rejects_invalid_state(): void
    {
        $this
            ->withSession(['auth.google_oauth_state' => 'expected-state'])
            ->get(route('auth.google.callback', [
                'state' => 'wrong-state',
                'code' => 'valid-code',
            ]))
            ->assertSessionHasErrors('google');

        $this->assertGuest();
    }
}
