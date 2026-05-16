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
            '/proker',
            '/proker/create',
            '/proker/templates',
            '/organization',
            '/organization/switcher',
            '/organization/periods',
            '/organization/calendar',
            '/tasks',
            '/tasks/kanban',
            '/tasks/calendar',
            '/tasks/assignments',
            '/finance',
            '/finance/budget-draft',
            '/finance/realization',
            '/finance/approval',
            '/reports',
            '/reports/proposal-editor',
            '/reports/lpj-checklist',
            '/documents',
            '/documents/folders',
            '/documents/upload-center',
            '/members',
            '/members/invites',
            '/members/roles',
            '/meetings',
            '/events/registrations',
            '/attendance',
            '/certificates',
            '/certificates/templates',
            '/certificates/issue',
            '/notifications',
            '/admin',
        ] as $path) {
            $this->get($path)->assertRedirect('/login');
        }
    }

    public function test_guest_is_redirected_from_internal_admin(): void
    {
        $this->get('/internal-admin')->assertRedirect();
    }
}
