<?php

declare(strict_types=1);

namespace App\Filament\Resources\PaymentOrders;

use App\Filament\Resources\PaymentOrders\Pages\ListPaymentOrders;
use App\Filament\SuperAdminGate;
use App\Models\PaymentOrder;
use BackedEnum;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

final class PaymentOrderResource extends Resource
{
    protected static ?string $model = PaymentOrder::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;

    protected static string|UnitEnum|null $navigationGroup = 'Operations';

    protected static ?int $navigationSort = 40;

    public static function canViewAny(): bool
    {
        return SuperAdminGate::canAccess();
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('provider_order_id')->label('Provider Order')->searchable(),
                TextColumn::make('amount')->money('IDR')->sortable(),
                TextColumn::make('status')->badge(),
                TextColumn::make('paid_at')->dateTime('Y-m-d H:i')->sortable(),
                TextColumn::make('expires_at')->dateTime('Y-m-d H:i')->sortable(),
                TextColumn::make('created_at')->dateTime('Y-m-d H:i')->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->options([
                    'pending' => 'Pending',
                    'paid' => 'Paid',
                    'expired' => 'Expired',
                    'refunded' => 'Refunded',
                ]),
            ])
            ->recordActions([ViewAction::make()])
            ->defaultSort('created_at', 'desc')
            ->emptyStateIcon('heroicon-o-credit-card')
            ->emptyStateHeading('Belum ada payment order')
            ->emptyStateDescription('Order pembayaran event akan tampil di sini.');
    }

    public static function getPages(): array
    {
        return ['index' => ListPaymentOrders::route('/')];
    }
}
