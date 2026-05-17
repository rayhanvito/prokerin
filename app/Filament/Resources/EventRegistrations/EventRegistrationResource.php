<?php

declare(strict_types=1);

namespace App\Filament\Resources\EventRegistrations;

use App\Filament\Resources\EventRegistrations\Pages\ListEventRegistrations;
use App\Filament\SuperAdminGate;
use App\Models\EventRegistration;
use BackedEnum;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

final class EventRegistrationResource extends Resource
{
    protected static ?string $model = EventRegistration::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTicket;

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

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('participant_name')->label('Participant')->searchable(),
                TextColumn::make('participant_email')->label('Email')->searchable(),
                TextColumn::make('project.name')->label('Project')->searchable(),
                TextColumn::make('institution')->toggleable(),
                TextColumn::make('status')->badge(),
                TextColumn::make('registered_at')->dateTime('Y-m-d H:i')->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->options([
                    'pending' => 'Pending',
                    'confirmed' => 'Confirmed',
                    'cancelled' => 'Cancelled',
                ]),
            ])
            ->recordActions([ViewAction::make()])
            ->defaultSort('registered_at', 'desc')
            ->emptyStateIcon('heroicon-o-ticket')
            ->emptyStateHeading('Belum ada event registration')
            ->emptyStateDescription('Registrasi peserta event lintas organisasi akan tampil di sini.');
    }

    public static function getPages(): array
    {
        return ['index' => ListEventRegistrations::route('/')];
    }
}
