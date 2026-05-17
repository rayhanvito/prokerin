<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Actions\Letter\GenerateLetterNumberAction;
use App\Domain\Letter\LetterType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class LetterNumberPatternTest extends TestCase
{
    use RefreshDatabase;

    public function test_letter_number_pattern_renders_sequence_roman_month_and_year(): void
    {
        $organizationId = (int) DB::table('organizations')->insertGetId([
            'name' => 'BEM FT',
            'slug' => 'bem-ft',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $number = app(GenerateLetterNumberAction::class)->execute(
            organizationId: $organizationId,
            letterType: LetterType::RoomReservation,
            year: 2026,
            month: 5,
            numberingPattern: 'B.{seq}/BEM-FT/{type_code}/{roman_month}/{year}',
        );

        $this->assertSame('B.001/BEM-FT/PR/V/2026', $number);
    }
}
