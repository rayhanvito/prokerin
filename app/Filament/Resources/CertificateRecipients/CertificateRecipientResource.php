<?php

declare(strict_types=1);

namespace App\Filament\Resources\CertificateRecipients;

use App\Filament\Resources\CertificateRecipients\Pages\ListCertificateRecipients;
use App\Filament\SuperAdminGate;
use App\Models\CertificateRecipient;
use BackedEnum;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

final class CertificateRecipientResource extends Resource
{
    protected static ?string $model = CertificateRecipient::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedIdentification;

    protected static string|UnitEnum|null $navigationGroup = 'Operations';

    protected static ?int $navigationSort = 80;

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
                TextColumn::make('certificate_number')->label('Number')->searchable(),
                TextColumn::make('recipient_name')->label('Recipient')->searchable(),
                TextColumn::make('recipient_email')->label('Email')->searchable(),
                TextColumn::make('organization.name')->label('Organization')->searchable(),
                TextColumn::make('issued_at')->dateTime('Y-m-d H:i')->sortable(),
                TextColumn::make('pdf_path')->label('PDF')->limit(28)->toggleable(),
            ])
            ->recordActions([ViewAction::make()])
            ->defaultSort('issued_at', 'desc')
            ->emptyStateIcon('heroicon-o-identification')
            ->emptyStateHeading('Belum ada certificate recipient')
            ->emptyStateDescription('Sertifikat yang diterbitkan akan tampil di sini.');
    }

    public static function getPages(): array
    {
        return ['index' => ListCertificateRecipients::route('/')];
    }
}
