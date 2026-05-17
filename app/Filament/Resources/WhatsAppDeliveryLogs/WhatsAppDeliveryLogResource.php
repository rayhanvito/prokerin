<?php

declare(strict_types=1);

namespace App\Filament\Resources\WhatsAppDeliveryLogs;

use App\Filament\Resources\WhatsAppDeliveryLogs\Pages\ListWhatsAppDeliveryLogs;
use App\Filament\SuperAdminGate;
use App\Models\WhatsAppDeliveryLog;
use BackedEnum;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

final class WhatsAppDeliveryLogResource extends Resource
{
    protected static ?string $model = WhatsAppDeliveryLog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static string|UnitEnum|null $navigationGroup = 'Operations';

    protected static ?string $navigationLabel = 'WhatsApp Logs';

    protected static ?int $navigationSort = 60;

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
                TextColumn::make('organization.name')->label('Organization')->searchable(),
                TextColumn::make('message_type')->badge()->searchable(),
                TextColumn::make('recipient_number')->label('Recipient')->searchable(),
                TextColumn::make('status')->badge(),
                TextColumn::make('sent_at')->dateTime('Y-m-d H:i')->sortable(),
                TextColumn::make('failed_at')->dateTime('Y-m-d H:i')->sortable(),
                TextColumn::make('created_at')->dateTime('Y-m-d H:i')->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->options([
                    'queued' => 'Queued',
                    'sent' => 'Sent',
                    'failed' => 'Failed',
                ]),
            ])
            ->recordActions([ViewAction::make()])
            ->defaultSort('created_at', 'desc')
            ->emptyStateIcon('heroicon-o-chat-bubble-left-right')
            ->emptyStateHeading('Belum ada WhatsApp log')
            ->emptyStateDescription('Delivery log WhatsApp akan tampil setelah job berjalan.');
    }

    public static function getPages(): array
    {
        return ['index' => ListWhatsAppDeliveryLogs::route('/')];
    }
}
