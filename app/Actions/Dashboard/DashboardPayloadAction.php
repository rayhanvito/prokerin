<?php

declare(strict_types=1);

namespace App\Actions\Dashboard;

use App\Actions\Dashboard\Variants\BendaharaDashboardPayloadAction;
use App\Actions\Dashboard\Variants\MemberDashboardPayloadAction;
use App\Actions\Dashboard\Variants\OperasionalDashboardPayloadAction;
use App\Actions\Dashboard\Variants\PimpinanDashboardPayloadAction;
use App\Actions\Dashboard\Variants\SekretarisDashboardPayloadAction;
use App\Enums\DashboardVariant;
use App\Models\User;

final readonly class DashboardPayloadAction
{
    public function __construct(
        private PimpinanDashboardPayloadAction $pimpinan,
        private SekretarisDashboardPayloadAction $sekretaris,
        private BendaharaDashboardPayloadAction $bendahara,
        private OperasionalDashboardPayloadAction $operasional,
        private MemberDashboardPayloadAction $member,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function execute(User $user, int $organizationId, DashboardVariant $variant): array
    {
        return match ($variant) {
            DashboardVariant::Pimpinan => $this->pimpinan->execute((int) $user->id, $organizationId),
            DashboardVariant::Sekretaris => $this->sekretaris->execute((int) $user->id, $organizationId),
            DashboardVariant::Bendahara => $this->bendahara->execute((int) $user->id, $organizationId),
            DashboardVariant::Operasional => $this->operasional->execute((int) $user->id, $organizationId),
            DashboardVariant::Member, DashboardVariant::Viewer => $this->member->execute((int) $user->id, $organizationId),
        };
    }
}
