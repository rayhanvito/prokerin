<?php

declare(strict_types=1);

namespace Tests\Feature\Security;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class AuthenticationBypassTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_from_authenticated_workspace_routes(): void
    {
        foreach ([
            '/dashboard',
            '/proker/create',
            '/finance',
        ] as $path) {
            $this->get($path)->assertRedirect('/login');
        }
    }

    public function test_guest_is_redirected_from_internal_admin(): void
    {
        $this->get('/internal-admin')->assertRedirect();
    }
}
