<?php

declare(strict_types=1);

namespace App\Support;

use App\Domain\Organization\Enums\OrganizationMode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

final readonly class OrganizationModeGate
{
    public function __construct(private OrganizationMode $mode) {}

    public static function fromValue(?string $mode): self
    {
        return new self(OrganizationMode::tryFrom((string) $mode) ?? OrganizationMode::Organization);
    }

    public static function forOrganization(int $organizationId): self
    {
        $mode = DB::table('organizations')
            ->where('id', $organizationId)
            ->value('mode');

        return self::fromValue(is_string($mode) ? $mode : null);
    }

    public static function forRequest(Request $request): self
    {
        $organizationId = self::activeOrganizationId($request);

        return $organizationId === null
            ? self::fromValue(null)
            : self::forOrganization($organizationId);
    }

    public function isKepanitiaan(): bool
    {
        return $this->mode === OrganizationMode::Kepanitiaan;
    }

    public function canUsePeriods(): bool
    {
        return ! $this->isKepanitiaan();
    }

    public function canUseHandover(): bool
    {
        return ! $this->isKepanitiaan();
    }

    public function canUseRoleMatrix(): bool
    {
        return ! $this->isKepanitiaan();
    }

    private static function activeOrganizationId(Request $request): ?int
    {
        $userId = $request->user()?->id;

        if ($userId === null) {
            return null;
        }

        $activeOrganizationId = $request->session()->get('active_organization_id');
        $query = DB::table('organization_members')
            ->where('user_id', $userId)
            ->when(is_numeric($activeOrganizationId), static function ($query) use ($activeOrganizationId): void {
                $query->where('organization_id', (int) $activeOrganizationId);
            })
            ->orderBy('id');

        $organizationId = $query->value('organization_id');

        if ($organizationId !== null) {
            return (int) $organizationId;
        }

        if (is_numeric($activeOrganizationId)) {
            $request->session()->forget('active_organization_id');
        }

        $fallbackId = DB::table('organization_members')
            ->where('user_id', $userId)
            ->orderBy('id')
            ->value('organization_id');

        return $fallbackId === null ? null : (int) $fallbackId;
    }
}
