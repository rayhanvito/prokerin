<?php

declare(strict_types=1);

namespace App\Actions\Workspace;

use Illuminate\Support\Facades\DB;

final class GetCertificatePayloadAction
{
    /**
     * @return array{
     *     metrics: array<int, array{label: string, value: string, note: string}>,
     *     templates: array<int, array{id: int, name: string, description: string|null, templateHtml: string, signatureLabel: string|null, signatureName: string|null, isActive: bool, issuedCount: int}>,
     *     certificates: array<int, array{id: int, certificateNumber: string, recipientName: string, recipientEmail: string|null, templateName: string, projectName: string|null, meetingTitle: string|null, issuedAt: string, hasPdf: bool, verifyUrl: string, downloadUrl: string}>,
     *     recipients: array<int, array{id: int, name: string, email: string, role: string}>,
     *     projects: array<int, array{id: int, name: string}>,
     *     meetings: array<int, array{id: int, title: string}>,
     *     canIssue: bool
     * }
     */
    public function execute(int $actorUserId): array
    {
        $memberships = DB::table('organization_members')
            ->where('user_id', $actorUserId)
            ->orderBy('id')
            ->get(['organization_id', 'role']);
        $organizationIds = $memberships->pluck('organization_id');
        $activeOrganizationId = $organizationIds->first();
        $canIssue = $memberships
            ->whereIn('role', ['organization_owner', 'organization_admin'])
            ->isNotEmpty();

        $templates = DB::table('certificate_templates')
            ->leftJoin('certificate_recipients', 'certificate_recipients.template_id', '=', 'certificate_templates.id')
            ->whereIn('certificate_templates.organization_id', $organizationIds)
            ->groupBy(
                'certificate_templates.id',
                'certificate_templates.name',
                'certificate_templates.description',
                'certificate_templates.template_html',
                'certificate_templates.signature_label',
                'certificate_templates.signature_name',
                'certificate_templates.is_active',
            )
            ->orderByDesc('certificate_templates.is_active')
            ->orderBy('certificate_templates.name')
            ->get([
                'certificate_templates.id',
                'certificate_templates.name',
                'certificate_templates.description',
                'certificate_templates.template_html',
                'certificate_templates.signature_label',
                'certificate_templates.signature_name',
                'certificate_templates.is_active',
                DB::raw('count(certificate_recipients.id) as issued_count'),
            ]);

        $certificates = DB::table('certificate_recipients')
            ->join('certificate_templates', 'certificate_templates.id', '=', 'certificate_recipients.template_id')
            ->leftJoin('projects', 'projects.id', '=', 'certificate_recipients.project_id')
            ->leftJoin('meetings', 'meetings.id', '=', 'certificate_recipients.meeting_id')
            ->whereIn('certificate_recipients.organization_id', $organizationIds)
            ->orderByDesc('certificate_recipients.issued_at')
            ->limit(20)
            ->get([
                'certificate_recipients.id',
                'certificate_recipients.certificate_number',
                'certificate_recipients.recipient_name',
                'certificate_recipients.recipient_email',
                'certificate_recipients.issued_at',
                'certificate_recipients.verification_token',
                'certificate_recipients.pdf_path',
                'certificate_templates.name as template_name',
                'projects.name as project_name',
                'meetings.title as meeting_title',
            ]);

        $recipients = DB::table('organization_members')
            ->join('users', 'users.id', '=', 'organization_members.user_id')
            ->where('organization_members.organization_id', $activeOrganizationId)
            ->orderBy('users.name')
            ->get(['users.id', 'users.name', 'users.email', 'organization_members.role']);

        $projects = DB::table('projects')
            ->where('organization_id', $activeOrganizationId)
            ->orderBy('name')
            ->get(['id', 'name']);

        $meetings = DB::table('meetings')
            ->where('organization_id', $activeOrganizationId)
            ->orderByDesc('starts_at')
            ->get(['id', 'title']);

        $issuedTotal = $certificates->count();
        $readyPdf = $certificates->filter(static fn (object $certificate): bool => filled($certificate->pdf_path))->count();

        return [
            'metrics' => [
                [
                    'label' => 'Sertifikat terbit',
                    'value' => (string) $issuedTotal,
                    'note' => 'Dari organisasi yang bisa diakses user',
                ],
                [
                    'label' => 'Template aktif',
                    'value' => (string) $templates->filter(static fn (object $template): bool => (bool) $template->is_active)->count(),
                    'note' => 'Siap dipakai batch issue',
                ],
                [
                    'label' => 'PDF siap',
                    'value' => (string) $readyPdf,
                    'note' => 'File sudah tersimpan di storage privat',
                ],
            ],
            'templates' => $templates
                ->map(static fn (object $template): array => [
                    'id' => (int) $template->id,
                    'name' => (string) $template->name,
                    'description' => $template->description === null ? null : (string) $template->description,
                    'templateHtml' => (string) $template->template_html,
                    'signatureLabel' => $template->signature_label === null ? null : (string) $template->signature_label,
                    'signatureName' => $template->signature_name === null ? null : (string) $template->signature_name,
                    'isActive' => (bool) $template->is_active,
                    'issuedCount' => (int) $template->issued_count,
                ])
                ->all(),
            'certificates' => $certificates
                ->map(static fn (object $certificate): array => [
                    'id' => (int) $certificate->id,
                    'certificateNumber' => (string) $certificate->certificate_number,
                    'recipientName' => (string) $certificate->recipient_name,
                    'recipientEmail' => $certificate->recipient_email === null ? null : (string) $certificate->recipient_email,
                    'templateName' => (string) $certificate->template_name,
                    'projectName' => $certificate->project_name === null ? null : (string) $certificate->project_name,
                    'meetingTitle' => $certificate->meeting_title === null ? null : (string) $certificate->meeting_title,
                    'issuedAt' => (string) $certificate->issued_at,
                    'hasPdf' => filled($certificate->pdf_path),
                    'verifyUrl' => route('certificates.verify', ['token' => $certificate->verification_token]),
                    'downloadUrl' => route('certificates.download', ['certificateNumber' => $certificate->certificate_number]),
                ])
                ->all(),
            'recipients' => $recipients
                ->map(static fn (object $recipient): array => [
                    'id' => (int) $recipient->id,
                    'name' => (string) $recipient->name,
                    'email' => (string) $recipient->email,
                    'role' => (string) $recipient->role,
                ])
                ->all(),
            'projects' => $projects
                ->map(static fn (object $project): array => [
                    'id' => (int) $project->id,
                    'name' => (string) $project->name,
                ])
                ->all(),
            'meetings' => $meetings
                ->map(static fn (object $meeting): array => [
                    'id' => (int) $meeting->id,
                    'title' => (string) $meeting->title,
                ])
                ->all(),
            'canIssue' => $canIssue,
        ];
    }
}
