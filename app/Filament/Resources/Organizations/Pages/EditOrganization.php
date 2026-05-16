<?php

declare(strict_types=1);

namespace App\Filament\Resources\Organizations\Pages;

use App\Actions\SuperAdmin\LogActivityAction;
use App\Filament\Resources\Organizations\OrganizationResource;
use App\Models\Organization;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditOrganization extends EditRecord
{
    protected static string $resource = OrganizationResource::class;

    protected ?string $planTierBefore = null;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
        ];
    }

    protected function beforeSave(): void
    {
        /** @var Organization $record */
        $record = $this->record;

        $current = $record->getRawOriginal('plan_tier');

        $this->planTierBefore = is_string($current) ? $current : null;
    }

    protected function afterSave(): void
    {
        /** @var Organization $record */
        $record = $this->record;

        $after = $record->getRawOriginal('plan_tier');

        $after = is_string($after) ? $after : null;

        if ($this->planTierBefore !== $after) {
            app(LogActivityAction::class)->execute('org.plan_tier.change', $record, [
                'before' => $this->planTierBefore,
                'after' => $after,
            ]);
        }
    }
}
