<?php

declare(strict_types=1);

namespace App\Filament\Resources\ActivityLogs;

use App\Filament\Resources\ActivityLogs\Pages\ListActivityLogs;
use App\Filament\SuperAdminGate;
use App\Models\ActivityLog;
use BackedEnum;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

final class ActivityLogResource extends Resource
{
    protected static ?string $model = ActivityLog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static string|UnitEnum|null $navigationGroup = 'Operations';

    protected static ?int $navigationSort = 10;

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
                TextColumn::make('created_at')->label('Time')->dateTime('Y-m-d H:i:s')->sortable(),
                TextColumn::make('user.name')->label('Actor')->searchable(),
                TextColumn::make('action')->badge()->searchable(),
                TextColumn::make('target_type')->label('Target')->formatStateUsing(static fn (string $state): string => class_basename($state))->searchable(),
                TextColumn::make('target_id')->label('Target ID')->sortable(),
                TextColumn::make('ip_address')->label('IP')->toggleable(),
            ])
            ->filters([
                SelectFilter::make('action')
                    ->options(static fn (): array => ActivityLog::query()->select('action')->distinct()->orderBy('action')->pluck('action', 'action')->all()),
                SelectFilter::make('target_type')
                    ->label('Target')
                    ->options(static fn (): array => ActivityLog::query()->select('target_type')->distinct()->orderBy('target_type')->pluck('target_type', 'target_type')->all()),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateIcon('heroicon-o-clipboard-document-check')
            ->emptyStateHeading('Belum ada audit log')
            ->emptyStateDescription('Aktivitas super admin akan muncul di sini.');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListActivityLogs::route('/'),
        ];
    }
}
