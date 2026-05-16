<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

final class LandingPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_landing_pages_render_without_authentication(): void
    {
        foreach ([
            route('landing.home') => 'Landing/Home',
            route('landing.features') => 'Landing/Features',
            route('landing.pricing') => 'Landing/Pricing',
        ] as $url => $component) {
            $this->get($url)
                ->assertOk()
                ->assertInertia(fn (AssertableInertia $page) => $page->component($component));
        }
    }

    public function test_dashboard_still_requires_authentication(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }
}
