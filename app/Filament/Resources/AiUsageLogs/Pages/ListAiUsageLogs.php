<?php

declare(strict_types=1);

namespace App\Filament\Resources\AiUsageLogs\Pages;

use App\Filament\Resources\AiUsageLogs\AiUsageLogResource;
use Filament\Resources\Pages\ListRecords;

final class ListAiUsageLogs extends ListRecords
{
    protected static string $resource = AiUsageLogResource::class;
}
