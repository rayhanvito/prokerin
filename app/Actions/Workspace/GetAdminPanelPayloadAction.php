<?php

declare(strict_types=1);

namespace App\Actions\Workspace;

use Illuminate\Support\Facades\DB;

final class GetAdminPanelPayloadAction
{
    /**
     * @return array{
     *     cards: array<int, array{title: string, description: string, status: string}>,
     *     resources: array<int, array{resource: string, owner: string, purpose: string, package: string, status: string}>,
     *     systemHealth: array{queuedExports: int, failedExports: int, pendingNotifications: int, filamentInstalled: bool}
     * }
     */
    public function execute(): array
    {
        $filamentInstalled = class_exists('Filament\\Panel');

        return [
            'cards' => [
                [
                    'title' => 'Organizations',
                    'description' => 'Internal overview untuk tenant organisasi.',
                    'status' => $filamentInstalled ? 'Ready for resource' : 'Filament pending',
                ],
                [
                    'title' => 'System Health',
                    'description' => 'Queue, storage, email, dan export worker status.',
                    'status' => 'DB-backed',
                ],
                [
                    'title' => 'Access Audit',
                    'description' => 'Audit role, policy, dan permission assignment.',
                    'status' => 'Planned',
                ],
            ],
            'resources' => [
                [
                    'resource' => 'OrganizationResource',
                    'owner' => 'Internal Admin',
                    'purpose' => 'Tenant monitoring',
                    'package' => 'Filament',
                    'status' => $filamentInstalled ? 'Ready' : 'Pending package',
                ],
                [
                    'resource' => 'UserResource',
                    'owner' => 'Internal Admin',
                    'purpose' => 'Account support',
                    'package' => 'Filament',
                    'status' => $filamentInstalled ? 'Ready' : 'Pending package',
                ],
                [
                    'resource' => 'DocumentExportResource',
                    'owner' => 'Ops',
                    'purpose' => 'PDF/DOCX queue monitoring',
                    'package' => 'Filament',
                    'status' => $filamentInstalled ? 'Ready' : 'Pending package',
                ],
            ],
            'systemHealth' => [
                'queuedExports' => DB::table('document_exports')->where('status', 'queued')->count(),
                'failedExports' => DB::table('document_exports')->where('status', 'failed')->count(),
                'pendingNotifications' => DB::table('notifications')->whereNull('read_at')->count(),
                'filamentInstalled' => $filamentInstalled,
            ],
        ];
    }
}
