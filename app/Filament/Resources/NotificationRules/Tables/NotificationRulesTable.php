<?php

declare(strict_types=1);

namespace App\Filament\Resources\NotificationRules\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class NotificationRulesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('event')
                    ->label('Event Type')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('label')
                    ->label('Label')
                    ->searchable(),

                TextColumn::make('organization.name')
                    ->label('Organization')
                    ->placeholder('Global default')
                    ->searchable(),

                TextColumn::make('channels')
                    ->label('Channels')
                    ->badge()
                    ->separator(','),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'enabled' => 'Enabled',
                        'disabled' => 'Disabled',
                        'planned' => 'Planned',
                    ]),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([])
            ->defaultSort('event', 'asc');
    }
}
