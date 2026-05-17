<?php

declare(strict_types=1);

namespace App\Filament\Resources\NotificationRules\Tables;

use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\HtmlString;

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
                Action::make('preview')
                    ->label('Preview')
                    ->icon('heroicon-o-eye')
                    ->modalHeading('Preview notifikasi user')
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->modalContent(static fn ($record): HtmlString => new HtmlString(sprintf(
                        '<div class="rounded border border-gray-200 bg-white p-4">
                            <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">%s</p>
                            <h3 class="mt-1 text-base font-semibold text-gray-950">%s</h3>
                            <p class="mt-2 text-sm text-gray-600">Contoh pesan untuk audience <strong>%s</strong>. Channel aktif: %s.</p>
                        </div>',
                        e((string) $record->event),
                        e((string) $record->label),
                        e((string) $record->audience),
                        e(implode(', ', is_array($record->channels) ? $record->channels : [])),
                    ))),
                EditAction::make(),
            ])
            ->toolbarActions([])
            ->defaultSort('event', 'asc');
    }
}
