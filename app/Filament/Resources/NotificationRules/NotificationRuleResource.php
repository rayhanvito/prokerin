<?php

declare(strict_types=1);

namespace App\Filament\Resources\NotificationRules;

use App\Filament\Resources\NotificationRules\Pages\EditNotificationRule;
use App\Filament\Resources\NotificationRules\Pages\ListNotificationRules;
use App\Filament\Resources\NotificationRules\Schemas\NotificationRuleForm;
use App\Filament\Resources\NotificationRules\Tables\NotificationRulesTable;
use App\Filament\SuperAdminGate;
use App\Models\NotificationRule;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class NotificationRuleResource extends Resource
{
    protected static ?string $model = NotificationRule::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBellAlert;

    protected static ?string $navigationLabel = 'Notification Rules';

    protected static ?int $navigationSort = 40;

    public static function canViewAny(): bool
    {
        return SuperAdminGate::canAccess();
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return NotificationRuleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return NotificationRulesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListNotificationRules::route('/'),
            'edit' => EditNotificationRule::route('/{record}/edit'),
        ];
    }
}
