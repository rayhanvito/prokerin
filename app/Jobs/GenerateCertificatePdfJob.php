<?php

declare(strict_types=1);

namespace App\Jobs;

use Dompdf\Dompdf;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

final class GenerateCertificatePdfJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $certificateRecipientId,
    ) {}

    public function handle(): void
    {
        $certificate = DB::table('certificate_recipients')
            ->join('certificate_templates', 'certificate_templates.id', '=', 'certificate_recipients.template_id')
            ->join('organizations', 'organizations.id', '=', 'certificate_recipients.organization_id')
            ->leftJoin('projects', 'projects.id', '=', 'certificate_recipients.project_id')
            ->leftJoin('meetings', 'meetings.id', '=', 'certificate_recipients.meeting_id')
            ->where('certificate_recipients.id', $this->certificateRecipientId)
            ->select([
                'certificate_recipients.*',
                'certificate_templates.name as template_name',
                'certificate_templates.template_html',
                'certificate_templates.signature_label',
                'certificate_templates.signature_name',
                'organizations.name as organization_name',
                'organizations.slug as organization_slug',
                'projects.name as project_name',
                'meetings.title as meeting_title',
            ])
            ->first();

        if ($certificate === null || filled($certificate->pdf_path)) {
            return;
        }

        $html = $this->renderHtml($certificate);
        $dompdf = new Dompdf;
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        $path = sprintf(
            'certificates/%s/%s.pdf',
            (string) $certificate->organization_slug,
            strtolower((string) $certificate->certificate_number),
        );

        Storage::disk('s3')->put($path, $dompdf->output());

        DB::table('certificate_recipients')
            ->where('id', $this->certificateRecipientId)
            ->update([
                'pdf_path' => $path,
                'updated_at' => now(),
            ]);
    }

    public function failed(Throwable $exception): void
    {
        DB::table('certificate_recipients')
            ->where('id', $this->certificateRecipientId)
            ->update(['updated_at' => now()]);
    }

    private function renderHtml(object $certificate): string
    {
        $replacements = [
            '{{recipient_name}}' => e((string) $certificate->recipient_name),
            '{{certificate_number}}' => e((string) $certificate->certificate_number),
            '{{organization_name}}' => e((string) $certificate->organization_name),
            '{{project_name}}' => e((string) ($certificate->project_name ?? 'Kegiatan organisasi')),
            '{{meeting_title}}' => e((string) ($certificate->meeting_title ?? '')),
            '{{issued_at}}' => e((string) $certificate->issued_at),
            '{{signature_label}}' => e((string) ($certificate->signature_label ?? 'Ketua Organisasi')),
            '{{signature_name}}' => e((string) ($certificate->signature_name ?? '')),
            '{{verification_url}}' => e(route('certificates.verify', ['token' => $certificate->verification_token])),
        ];

        $body = strtr((string) $certificate->template_html, $replacements);

        return sprintf(
            '<!doctype html><html><head><meta charset="utf-8"><style>body{font-family:DejaVu Sans,sans-serif;color:#242934;margin:0}.certificate{min-height:680px;border:12px solid #24695c;padding:56px;text-align:center}h1{font-size:34px;margin:0 0 20px;color:#24695c}.recipient{font-size:32px;font-weight:700;margin:24px 0}.meta{color:#59667a;font-size:13px}.signature{margin-top:56px;color:#242934}</style></head><body><main class="certificate">%s</main></body></html>',
            $body,
        );
    }
}
