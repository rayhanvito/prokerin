<?php

declare(strict_types=1);

namespace App\Filament\Resources\DocumentExports\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DocumentExportsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('organization_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('project_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('requested_by_user_id')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('document_title')
                    ->searchable(),
                TextColumn::make('document_type')
                    ->searchable(),
                TextColumn::make('format')
                    ->searchable(),
                TextColumn::make('queue_name')
                    ->searchable(),
                TextColumn::make('engine')
                    ->searchable(),
                TextColumn::make('storage_disk')
                    ->searchable(),
                TextColumn::make('artifact_status')
                    ->label('Artifact')
                    ->badge()
                    ->state(static fn ($record): string => filled($record->output_path) ? 'Stored' : 'Pending')
                    ->color(static fn (string $state): string => $state === 'Stored' ? 'success' : 'gray'),
                TextColumn::make('status')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([]);
    }
}
