<?php

declare(strict_types=1);

namespace App\Actions\Certificate;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class CertificateNumberGenerator
{
    public function generate(int $organizationId, int $year): string
    {
        $slug = (string) DB::table('organizations')
            ->where('id', $organizationId)
            ->value('slug');
        $orgCode = strtoupper((string) Str::of($slug)->replace('-', '')->substr(0, 12));
        $prefix = sprintf('PRK-%d-%s-', $year, $orgCode === '' ? 'ORG' : $orgCode);
        $lastNumber = DB::table('certificate_recipients')
            ->where('organization_id', $organizationId)
            ->where('certificate_number', 'like', $prefix.'%')
            ->orderByDesc('certificate_number')
            ->value('certificate_number');

        $sequence = 1;

        if (is_string($lastNumber) && preg_match('/-(\d{4})$/', $lastNumber, $matches) === 1) {
            $sequence = ((int) $matches[1]) + 1;
        }

        do {
            $certificateNumber = $prefix.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT);
            $sequence++;
        } while (DB::table('certificate_recipients')->where('certificate_number', $certificateNumber)->exists());

        return $certificateNumber;
    }
}
