<?php

declare(strict_types=1);

namespace App\Filament\Resources\AiUsageLogs;

use App\Filament\Resources\AiUsageLogs\Pages\ListAiUsageLogs;
use App\Filament\SuperAdminGate;
use App\Models\AiUsageLog;
use BackedEnum;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

final class AiUsageLogResource extends Resource
{
    protected static ?string $model = AiUsageLog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCpuChip;

    protected static string|UnitEnum|null $navigationGroup = 'Operations';

    protected static ?string $navigationLabel = 'AI Usage';

    protected static ?int $navigationSort = 70;

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
                TextColumn::make('action_type')->badge()->searchable(),
                TextColumn::make('provider')->badge(),
                TextColumn::make('model')->toggleable(),
                TextColumn::make('prompt_tokens')->numeric()->sortable(),
                TextColumn::make('completion_tokens')->numeric()->sortable(),
                TextColumn::make('created_at')->dateTime('Y-m-d H:i')->sortable(),
            ])
            ->filters([
                SelectFilter::make('action_type')
                    ->options(static fn (): array => AiUsageLog::query()->select('action_type')->distinct()->pluck('action_type', 'action_type')->all()),
                SelectFilter::make('provider')
                    ->options(static fn (): array => AiUsageLog::query()->select('provider')->distinct()->pluck('provider', 'provider')->all()),
            ])
            ->recordActions([ViewAction::make()])
            ->defaultSort('created_at', 'desc')
            ->emptyStateIcon('heroicon-o-cpu-chip')
            ->emptyStateHeading('Belum ada AI usage')
            ->emptyStateDescription('Log penggunaan AI akan tampil setelah fitur AI dipakai.');
    }

    public static function getPages(): array
    {
        return ['index' => ListAiUsageLogs::route('/')];
    }
}
