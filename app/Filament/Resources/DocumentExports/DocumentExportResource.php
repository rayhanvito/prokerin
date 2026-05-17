<?php

declare(strict_types=1);

namespace App\Filament\Resources\DocumentExports;

use App\Filament\Resources\DocumentExports\Pages\ListDocumentExports;
use App\Filament\Resources\DocumentExports\Pages\ViewDocumentExport;
use App\Filament\Resources\DocumentExports\Schemas\DocumentExportInfolist;
use App\Filament\Resources\DocumentExports\Tables\DocumentExportsTable;
use App\Filament\SuperAdminGate;
use App\Models\DocumentExport;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class DocumentExportResource extends Resource
{
    protected static ?string $model = DocumentExport::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|UnitEnum|null $navigationGroup = 'Operations';

    protected static ?int $navigationSort = 50;

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

    public static function infolist(Schema $schema): Schema
    {
        return DocumentExportInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DocumentExportsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDocumentExports::route('/'),
            'view' => ViewDocumentExport::route('/{record}'),
        ];
    }
}
