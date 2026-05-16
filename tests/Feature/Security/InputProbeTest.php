<?php

declare(strict_types=1);

namespace Tests\Feature\Security;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

final class InputProbeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_search_probe_does_not_expose_database_errors(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();

        $this->actingAs($owner)
            ->get(route('organization.sponsors-vendors', ['search' => "'; DROP TABLE organizations; --"]))
            ->assertOk();

        $this->assertDatabaseHas('organizations', [
            'slug' => 'bem-fakultas-teknologi',
        ]);
    }

    public function test_project_slug_probe_returns_not_found_without_sql_error(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();

        $this->actingAs($owner)
            ->get('/proker/%27%20OR%20%271%27%3D%271')
            ->assertNotFound();
    }

    public function test_budget_amount_probe_is_rejected_as_non_numeric(): void
    {
        Storage::fake('s3');

        $treasurer = User::query()->where('email', 'bendahara@prokerin.test')->firstOrFail();
        $budgetLineId = (int) DB::table('budget_lines')
            ->where('name', 'Publikasi dan printing')
            ->value('id');

        $this->actingAs($treasurer)
            ->post(route('finance.realizations.store', ['budgetLine' => $budgetLineId]), [
                'name' => 'Probe amount',
                'amount' => '1; DELETE FROM budget_lines',
                'receipt' => UploadedFile::fake()->create('receipt.jpg', 128, 'image/jpeg'),
            ])
            ->assertSessionHasErrors('amount');

        $this->assertDatabaseHas('budget_lines', [
            'id' => $budgetLineId,
        ]);
    }
}
