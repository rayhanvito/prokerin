<?php

declare(strict_types=1);

namespace App\Filament\Resources\PaymentOrders\Pages;

use App\Filament\Resources\PaymentOrders\PaymentOrderResource;
use Filament\Resources\Pages\ListRecords;

final class ListPaymentOrders extends ListRecords
{
    protected static string $resource = PaymentOrderResource::class;
}
