<?php

declare(strict_types=1);

namespace App\Filament\Resources\NotificationRules\Pages;

use App\Filament\Resources\NotificationRules\NotificationRuleResource;
use Filament\Resources\Pages\EditRecord;

class EditNotificationRule extends EditRecord
{
    protected static string $resource = NotificationRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
