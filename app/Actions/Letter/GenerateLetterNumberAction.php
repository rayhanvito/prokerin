<?php

declare(strict_types=1);

namespace App\Actions\Letter;

use App\Domain\Letter\LetterType;
use Illuminate\Support\Facades\DB;

final class GenerateLetterNumberAction
{
    public function execute(
        int $organizationId,
        LetterType $letterType,
        int $year,
        int $month,
        string $numberingPattern,
    ): string {
        return DB::transaction(function () use ($organizationId, $letterType, $year, $month, $numberingPattern): string {
            DB::table('letter_number_sequences')->updateOrInsert(
                [
                    'organization_id' => $organizationId,
                    'year' => $year,
                    'month' => $month,
                ],
                [
                    'updated_at' => now(),
                    'created_at' => now(),
                ],
            );

            $sequence = DB::table('letter_number_sequences')
                ->where('organization_id', $organizationId)
                ->where('year', $year)
                ->where('month', $month)
                ->lockForUpdate()
                ->value('sequence');

            $nextSequence = ((int) $sequence) + 1;

            DB::table('letter_number_sequences')
                ->where('organization_id', $organizationId)
                ->where('year', $year)
                ->where('month', $month)
                ->update([
                    'sequence' => $nextSequence,
                    'updated_at' => now(),
                ]);

            return strtr($numberingPattern, [
                '{seq}' => str_pad((string) $nextSequence, 3, '0', STR_PAD_LEFT),
                '{roman_month}' => $this->romanMonth($month),
                '{year}' => (string) $year,
                '{type_code}' => $letterType->typeCode(),
            ]);
        });
    }

    private function romanMonth(int $month): string
    {
        return [
            1 => 'I',
            2 => 'II',
            3 => 'III',
            4 => 'IV',
            5 => 'V',
            6 => 'VI',
            7 => 'VII',
            8 => 'VIII',
            9 => 'IX',
            10 => 'X',
            11 => 'XI',
            12 => 'XII',
        ][$month] ?? 'I';
    }
}
