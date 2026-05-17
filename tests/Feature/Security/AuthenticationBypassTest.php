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

    public function test_guests_are_redirected_from_authenticated_workspace_mutation_routes(): void
    {
        foreach ([
            ['post', '/proker'],
            ['post', '/proker/templates/seminar/generate'],
            ['patch', '/proker/seminar-karier-digital'],
            ['delete', '/proker/seminar-karier-digital'],
            ['post', '/organization/logo'],
            ['post', '/organization/handover'],
            ['post', '/organization/sponsors-vendors'],
            ['patch', '/organization/sponsors-vendors/1'],
            ['patch', '/tasks/1/status'],
            ['post', '/finance/budget-lines/1/realizations'],
            ['patch', '/finance/budget-lines/1/approval'],
            ['patch', '/reports/proposal-drafts/1'],
            ['post', '/reports/proposal-drafts/1/submit'],
            ['patch', '/reports/proposal-drafts/1/decision'],
            ['post', '/reports/lpj/1/review'],
            ['patch', '/reports/lpj/1/decision'],
            ['patch', '/members/1/role'],
            ['post', '/meetings'],
            ['patch', '/meetings/1'],
            ['patch', '/meetings/1/minutes'],
            ['post', '/meetings/1/exports'],
            ['post', '/attendance/check-in'],
            ['post', '/attendance/sessions/1/manual-check-in'],
            ['post', '/attendance/sessions/1/qr-tokens'],
            ['delete', '/attendance/qr-tokens/1'],
            ['post', '/certificates/templates'],
            ['put', '/certificates/templates/1'],
            ['post', '/certificates/issue'],
            ['post', '/notifications/task-deadline-reminders'],
            ['post', '/notifications/meeting-alerts'],
            ['post', '/webpush/subscribe'],
            ['delete', '/webpush/subscribe'],
            ['patch', '/approval-workflows/1/decision'],
            ['patch', '/approval-workflows/1/delegate'],
            ['post', '/onboarding/complete'],
        ] as [$method, $path]) {
            $this->{$method}($path)->assertRedirect('/login');
        }
    }
}
