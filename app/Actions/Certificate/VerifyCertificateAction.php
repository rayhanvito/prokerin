<?php

declare(strict_types=1);

namespace App\Actions\Certificate;

use Illuminate\Support\Facades\DB;

final class VerifyCertificateAction
{
    /**
     * @return array{isValid: bool, certificate: array{certificateNumber: string, recipientName: string, recipientEmail: string|null, templateName: string, organizationName: string, projectName: string|null, meetingTitle: string|null, issuedAt: string, signatureLabel: string|null, signatureName: string|null, hasPdf: bool}|null}
     */
    public function execute(string $verificationToken): array
    {
        $certificate = DB::table('certificate_recipients')
            ->join('certificate_templates', 'certificate_templates.id', '=', 'certificate_recipients.template_id')
            ->join('organizations', 'organizations.id', '=', 'certificate_recipients.organization_id')
            ->leftJoin('projects', 'projects.id', '=', 'certificate_recipients.project_id')
            ->leftJoin('meetings', 'meetings.id', '=', 'certificate_recipients.meeting_id')
            ->where('certificate_recipients.verification_token', $verificationToken)
            ->select([
                'certificate_recipients.certificate_number',
                'certificate_recipients.recipient_name',
                'certificate_recipients.recipient_email',
                'certificate_recipients.issued_at',
                'certificate_recipients.pdf_path',
                'certificate_templates.name as template_name',
                'certificate_templates.signature_label',
                'certificate_templates.signature_name',
                'organizations.name as organization_name',
                'projects.name as project_name',
                'meetings.title as meeting_title',
            ])
            ->first();

        if ($certificate === null) {
            return [
                'isValid' => false,
                'certificate' => null,
            ];
        }

        return [
            'isValid' => true,
            'certificate' => [
                'certificateNumber' => (string) $certificate->certificate_number,
                'recipientName' => (string) $certificate->recipient_name,
                'recipientEmail' => $certificate->recipient_email === null ? null : (string) $certificate->recipient_email,
                'templateName' => (string) $certificate->template_name,
                'organizationName' => (string) $certificate->organization_name,
                'projectName' => $certificate->project_name === null ? null : (string) $certificate->project_name,
                'meetingTitle' => $certificate->meeting_title === null ? null : (string) $certificate->meeting_title,
                'issuedAt' => (string) $certificate->issued_at,
                'signatureLabel' => $certificate->signature_label === null ? null : (string) $certificate->signature_label,
                'signatureName' => $certificate->signature_name === null ? null : (string) $certificate->signature_name,
                'hasPdf' => filled($certificate->pdf_path),
            ],
        ];
    }
}
