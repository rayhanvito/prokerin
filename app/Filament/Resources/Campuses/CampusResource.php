<?php

declare(strict_types=1);

namespace App\Filament\Resources\Campuses;

use App\Filament\Resources\Campuses\Pages\ListCampuses;
use App\Filament\SuperAdminGate;
use App\Models\Campus;
use BackedEnum;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

final class CampusResource extends Resource
{
    protected static ?string $model = Campus::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;

    protected static string|UnitEnum|null $navigationGroup = 'Platform';

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
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('domain')->searchable(),
                TextColumn::make('adminUser.email')->label('Admin')->searchable(),
                TextColumn::make('created_at')->dateTime('Y-m-d H:i')->sortable(),
            ])
            ->recordActions([ViewAction::make()])
            ->defaultSort('created_at', 'desc')
            ->emptyStateIcon('heroicon-o-academic-cap')
            ->emptyStateHeading('Belum ada campus')
            ->emptyStateDescription('Campus yang terhubung ke Prokerin akan tampil di sini.');
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'domain', 'adminUser.email'];
    }

    public static function getPages(): array
    {
        return ['index' => ListCampuses::route('/')];
    }
}
