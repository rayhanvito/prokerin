<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Jobs\GenerateCertificatePdfJob;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

final class DigitalCertificateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_certificates_page_receives_tenant_scoped_payload(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();

        $this->actingAs($owner)
            ->get(route('certificates.index'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Certificates/Index')
                ->has('metrics', 3)
                ->where('metrics.0.value', '2')
                ->where('metrics.1.value', '1')
                ->where('metrics.2.value', '1')
                ->has('certificates', 2)
                ->where('certificates.0.recipientName', 'Raka Pratama')
                ->where('canIssue', true));
    }

    public function test_owner_can_create_certificate_template(): void
    {
        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();

        $this->actingAs($owner)
            ->post(route('certificates.templates.store'), [
                'name' => 'Sertifikat Prestasi',
                'description' => 'Untuk apresiasi pemenang lomba.',
                'template_html' => '<h1>{{recipient_name}}</h1><p>{{certificate_number}}</p>',
                'signature_label' => 'Ketua',
                'signature_name' => 'Dimas Aji',
                'is_active' => true,
            ])
            ->assertRedirect()
            ->assertSessionHas('success', 'Template sertifikat berhasil dibuat.');

        $this->assertDatabaseHas('certificate_templates', [
            'organization_id' => $this->organizationId('bem-fakultas-teknologi'),
            'name' => 'Sertifikat Prestasi',
            'is_active' => true,
        ]);
    }

    public function test_owner_can_issue_certificate_batch_and_queue_pdf_generation(): void
    {
        Queue::fake();

        $owner = User::query()->where('email', 'owner@prokerin.test')->firstOrFail();
        $member = User::query()->where('email', 'member@prokerin.test')->firstOrFail();

        $this->actingAs($owner)
            ->post(route('certificates.issue.store'), [
                'template_id' => $this->certificateTemplateId('Sertifikat Partisipasi Proker'),
                'project_id' => $this->projectId('seminar-karier-digital'),
                'meeting_id' => $this->meetingId('Technical Meeting Seminar Karier'),
                'recipients' => [
                    [
                        'user_id' => $member->id,
                        'recipient_name' => 'Ardi Saputra',
                        'recipient_email' => 'member@prokerin.test',
                    ],
                    [
                        'recipient_name' => 'Peserta Eksternal',
                        'recipient_email' => 'eksternal@kampus.test',
                    ],
                ],
            ])
            ->assertRedirect()
            ->assertSessionHas('success', '2 sertifikat berhasil diterbitkan dan PDF masuk antrean.');

        $this->assertDatabaseHas('certificate_recipients', [
            'recipient_name' => 'Ardi Saputra',
            'certificate_number' => 'PRK-2026-BEMFAKULTAST-0003',
            'pdf_path' => null,
        ]);
        $this->assertDatabaseHas('certificate_recipients', [
            'recipient_name' => 'Peserta Eksternal',
            'certificate_number' => 'PRK-2026-BEMFAKULTAST-0004',
            'pdf_path' => null,
        ]);

        Queue::assertPushed(GenerateCertificatePdfJob::class, 2);
    }

    public function test_certificate_pdf_job_generates_pdf_and_stores_path(): void
    {
        Storage::fake('s3');

        $certificateId = (int) DB::table('certificate_recipients')
            ->where('certificate_number', 'PRK-2026-BEMFAKULTAST-0002')
            ->value('id');

        (new GenerateCertificatePdfJob($certificateId))->handle();

        $path = (string) DB::table('certificate_recipients')
            ->where('id', $certificateId)
            ->value('pdf_path');

        Storage::disk('s3')->assertExists($path);
        $this->assertStringStartsWith('%PDF', Storage::disk('s3')->get($path));
    }

    public function test_public_verification_route_is_accessible_without_authentication(): void
    {
        $this->get(route('certificates.verify', ['token' => '11111111-1111-4111-8111-111111111111']))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Certificates/Verify')
                ->where('isValid', true)
                ->where('certificate.recipientName', 'Salsa Kirana')
                ->where('certificate.certificateNumber', 'PRK-2026-BEMFAKULTAST-0001'));
    }

    public function test_cross_tenant_user_cannot_download_other_organization_certificate(): void
    {
        Storage::fake('s3');

        $viewer = User::query()->where('email', 'test@example.com')->firstOrFail();

        $this->actingAs($viewer)
            ->get(route('certificates.download', ['certificateNumber' => 'PRK-2026-BEMFAKULTAST-0001']))
            ->assertNotFound();
    }

    public function test_workspace_member_can_download_completed_certificate_pdf(): void
    {
        Storage::fake('s3');

        $secretary = User::query()->where('email', 'sekretaris@prokerin.test')->firstOrFail();

        $response = $this->actingAs($secretary)
            ->get(route('certificates.download', ['certificateNumber' => 'PRK-2026-BEMFAKULTAST-0001']));

        $response->assertRedirect();
        $location = (string) $response->headers->get('Location');

        $this->assertStringContainsString('prk-2026-bemfakultast-0001.pdf', $location);
        $this->assertStringContainsString('expiration=', $location);
    }

    public function test_non_owner_admin_cannot_issue_certificates(): void
    {
        $member = User::query()->where('email', 'member@prokerin.test')->firstOrFail();

        $this->actingAs($member)
            ->post(route('certificates.issue.store'), [
                'template_id' => $this->certificateTemplateId('Sertifikat Partisipasi Proker'),
                'recipients' => [
                    [
                        'user_id' => $member->id,
                        'recipient_name' => 'Ardi Saputra',
                        'recipient_email' => 'member@prokerin.test',
                    ],
                ],
            ])
            ->assertForbidden();
    }

    private function organizationId(string $slug): int
    {
        return (int) DB::table('organizations')->where('slug', $slug)->value('id');
    }

    private function certificateTemplateId(string $name): int
    {
        return (int) DB::table('certificate_templates')->where('name', $name)->value('id');
    }

    private function projectId(string $slug): int
    {
        return (int) DB::table('projects')->where('slug', $slug)->value('id');
    }

    private function meetingId(string $title): int
    {
        return (int) DB::table('meetings')->where('title', $title)->value('id');
    }
}
