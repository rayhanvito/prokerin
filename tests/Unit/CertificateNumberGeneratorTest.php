<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Actions\Certificate\CertificateNumberGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class CertificateNumberGeneratorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_it_generates_sequential_certificate_numbers_per_organization_and_year(): void
    {
        $generator = new CertificateNumberGenerator;
        $organizationId = $this->organizationId('bem-fakultas-teknologi');

        $first = $generator->generate($organizationId, 2026);
        $this->assertSame('PRK-2026-BEMFAKULTAST-0003', $first);

        DB::table('certificate_recipients')->insert([
            'organization_id' => $organizationId,
            'template_id' => $this->certificateTemplateId('Sertifikat Partisipasi Proker'),
            'recipient_name' => 'Peserta Baru',
            'recipient_email' => 'peserta@prokerin.test',
            'certificate_number' => $first,
            'issued_at' => now(),
            'verification_token' => '33333333-3333-4333-8333-333333333333',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $second = $generator->generate($organizationId, 2026);
        $this->assertSame('PRK-2026-BEMFAKULTAST-0004', $second);
    }

    public function test_sequence_is_scoped_to_organization(): void
    {
        $generator = new CertificateNumberGenerator;

        $this->assertSame(
            'PRK-2026-HIMAINFORMAT-0001',
            $generator->generate($this->organizationId('hima-informatika'), 2026),
        );
    }

    private function organizationId(string $slug): int
    {
        return (int) DB::table('organizations')->where('slug', $slug)->value('id');
    }

    private function certificateTemplateId(string $name): int
    {
        return (int) DB::table('certificate_templates')->where('name', $name)->value('id');
    }
}
