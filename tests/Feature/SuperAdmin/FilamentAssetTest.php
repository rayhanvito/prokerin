<?php

declare(strict_types=1);

namespace Tests\Feature\SuperAdmin;

use Tests\TestCase;

final class FilamentAssetTest extends TestCase
{
    public function test_filament_assets_are_published_for_internal_admin_ui(): void
    {
        $this->assertFileExists(public_path('css/filament/filament/app.css'));
        $this->assertFileExists(public_path('js/filament/filament/app.js'));
        $this->assertFileExists(public_path('fonts/filament/filament/inter/index.css'));
    }
}
