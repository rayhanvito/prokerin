<?php

declare(strict_types=1);

namespace App\Filament\Resources\DocumentExports\Tables;

use Filament\Actions\EditAction;
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
                TextColumn::make('output_path')
                    ->searchable(),
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
                EditAction::make(),
            ])
            ->toolbarActions([]);
    }
}
