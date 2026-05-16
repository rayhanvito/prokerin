<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

final class AdminPanelPayloadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_admin_page_receives_backend_readiness_payload(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();

        $response = $this->actingAs($owner)->get(route('admin.index'));

        $response->assertOk();
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('Admin/Index')
            ->has('cards', 3)
            ->has('resources', 3)
            ->where('systemHealth.filamentInstalled', false)
            ->where('resources.0.status', 'Pending package'));
    }
}
