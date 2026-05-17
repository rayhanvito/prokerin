<?php

declare(strict_types=1);

namespace App\Filament\Resources\DocumentExports\Tables;

use App\Actions\DocumentExport\RetryDocumentExportAction;
use App\Models\DocumentExport;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class DocumentExportsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('document_title')
                    ->label('Title')
                    ->searchable(),
                TextColumn::make('document_type')
                    ->label('Type')
                    ->badge()
                    ->searchable(),
                TextColumn::make('format')
                    ->label('Format')
                    ->badge(),
                TextColumn::make('queue_name')
                    ->label('Queue')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('engine')
                    ->label('Engine')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('storage_disk')
                    ->label('Disk')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('artifact_status')
                    ->label('Artifact')
                    ->badge()
                    ->state(static fn ($record): string => filled($record->output_path) ? 'Stored' : 'Pending')
                    ->color(static fn (string $state): string => $state === 'Stored' ? 'success' : 'gray'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(static fn (string $state): string => match ($state) {
                        'completed' => 'success',
                        'failed' => 'danger',
                        'processing' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'queued' => 'Queued',
                        'processing' => 'Processing',
                        'completed' => 'Completed',
                        'failed' => 'Failed',
                    ]),
                SelectFilter::make('document_type')
                    ->label('Document Type')
                    ->options(static fn (): array => DocumentExport::query()
                        ->select('document_type')
                        ->distinct()
                        ->pluck('document_type', 'document_type')
                        ->all()),
            ])
            ->recordActions([
                ViewAction::make(),
                Action::make('retry')
                    ->label('Retry')
                    ->icon('heroicon-o-arrow-path')
                    ->color('success')
                    ->visible(static fn (DocumentExport $record): bool => (string) $record->status === 'failed')
                    ->requiresConfirmation()
                    ->modalHeading('Retry export')
                    ->modalDescription('Export ini akan diqueue ulang.')
                    ->action(static function (DocumentExport $record): void {
                        app(RetryDocumentExportAction::class)->execute($record, (int) auth()->id());

                        Notification::make()
                            ->success()
                            ->title('Export re-queued')
                            ->body(sprintf('"%s" akan dijalankan ulang.', (string) $record->document_title))
                            ->send();
                    }),
            ])
            ->toolbarActions([])
            ->defaultSort('created_at', 'desc');
    }
}
