<?php

declare(strict_types=1);

namespace App\Filament\Resources\WhatsAppDeliveryLogs\Pages;

use App\Filament\Resources\WhatsAppDeliveryLogs\WhatsAppDeliveryLogResource;
use Filament\Resources\Pages\ListRecords;

final class ListWhatsAppDeliveryLogs extends ListRecords
{
    protected static string $resource = WhatsAppDeliveryLogResource::class;
}
