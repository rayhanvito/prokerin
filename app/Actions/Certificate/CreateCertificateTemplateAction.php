<?php

declare(strict_types=1);

namespace App\Actions\Certificate;

use Illuminate\Support\Facades\DB;

final class CreateCertificateTemplateAction
{
    /**
     * @param  array{name: string, description?: string|null, template_html: string, signature_label?: string|null, signature_name?: string|null, is_active?: bool}  $data
     */
    public function execute(int $actorUserId, array $data, ?int $templateId = null): int
    {
        $organizationId = $templateId === null
            ? $this->manageableOrganizationId($actorUserId)
            : $this->manageableTemplateOrganizationId($actorUserId, $templateId);

        abort_if($organizationId === null, 403);

        $now = now();
        $payload = [
            'organization_id' => $organizationId,
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'template_html' => $data['template_html'],
            'signature_label' => $data['signature_label'] ?? null,
            'signature_name' => $data['signature_name'] ?? null,
            'is_active' => (bool) ($data['is_active'] ?? true),
            'updated_at' => $now,
        ];

        if ($templateId !== null) {
            DB::table('certificate_templates')
                ->where('id', $templateId)
                ->where('organization_id', $organizationId)
                ->update($payload);

            return $templateId;
        }

        return (int) DB::table('certificate_templates')->insertGetId([
            ...$payload,
            'created_at' => $now,
        ]);
    }

    private function manageableOrganizationId(int $actorUserId): ?int
    {
        $organizationId = DB::table('organization_members')
            ->where('user_id', $actorUserId)
            ->whereIn('role', ['organization_owner', 'organization_admin'])
            ->orderBy('id')
            ->value('organization_id');

        return $organizationId === null ? null : (int) $organizationId;
    }

    private function manageableTemplateOrganizationId(int $actorUserId, int $templateId): ?int
    {
        $organizationId = DB::table('certificate_templates')
            ->join('organization_members', 'organization_members.organization_id', '=', 'certificate_templates.organization_id')
            ->where('certificate_templates.id', $templateId)
            ->where('organization_members.user_id', $actorUserId)
            ->whereIn('organization_members.role', ['organization_owner', 'organization_admin'])
            ->value('certificate_templates.organization_id');

        return $organizationId === null ? null : (int) $organizationId;
    }
}
