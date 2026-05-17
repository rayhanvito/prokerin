<?php

declare(strict_types=1);

namespace App\Actions\Organization;

use App\Actions\Workspace\GetActiveOrganizationContextAction;
use App\Support\Roles;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;

final class UpdateOrganizationAction
{
    public function __construct(
        private readonly GetActiveOrganizationContextAction $activeOrganizationContext,
    ) {}

    /**
     * @param  array{name: string, description?: string|null}  $data
     *
     * @throws AuthorizationException
     */
    public function execute(int $actorUserId, array $data): void
    {
        $activeOrganizationId = session('active_organization_id');
        $context = $this->activeOrganizationContext->execute(
            $actorUserId,
            is_numeric($activeOrganizationId) ? (int) $activeOrganizationId : null,
        );

        if ($context->role !== Roles::ORGANIZATION_OWNER) {
            throw new AuthorizationException('You are not allowed to update this organization.');
        }

        DB::table('organizations')
            ->where('id', $context->organizationId)
            ->update([
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'updated_at' => now(),
            ]);
    }
}
