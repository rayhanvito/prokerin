<?php

declare(strict_types=1);

namespace App\Filament\Resources\FailedJobs\Tables;

use App\Actions\SuperAdmin\LogActivityAction;
use App\Models\FailedJob;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Artisan;

class FailedJobsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('uuid')
                    ->label('UUID')
                    ->limit(12)
                    ->copyable()
                    ->copyMessage('UUID copied'),

                TextColumn::make('job_name')
                    ->label('Job')
                    ->state(static fn (FailedJob $record): string => $record->job_name)
                    ->searchable(query: static function ($query, string $search) {
                        $query->where('payload', 'like', '%'.$search.'%');
                    }),

                TextColumn::make('queue')
                    ->label('Queue')
                    ->sortable(),

                TextColumn::make('connection')
                    ->label('Connection')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('exception_first_line')
                    ->label('Exception')
                    ->state(static fn (FailedJob $record): string => $record->exception_first_line)
                    ->wrap()
                    ->limit(120),

                TextColumn::make('failed_at')
                    ->label('Failed At')
                    ->dateTime('Y-m-d H:i:s')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('queue')
                    ->options(static fn (): array => FailedJob::query()
                        ->select('queue')
                        ->distinct()
                        ->pluck('queue', 'queue')
                        ->all()),
            ])
            ->recordActions([
                Action::make('retry')
                    ->label('Retry')
                    ->icon('heroicon-o-arrow-path')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Retry failed job')
                    ->modalDescription('Job ini akan masuk ulang ke antrean.')
                    ->action(static function (FailedJob $record): void {
                        Artisan::call('queue:retry', ['id' => [$record->uuid]]);

                        app(LogActivityAction::class)->execute('failed_job.retry', $record, [
                            'uuid' => (string) $record->uuid,
                            'job' => $record->job_name,
                        ]);

                        Notification::make()
                            ->success()
                            ->title('Job re-queued')
                            ->body(sprintf('%s di-retry.', $record->job_name))
                            ->send();
                    }),

                Action::make('delete')
                    ->label('Delete')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Delete failed job')
                    ->action(static function (FailedJob $record): void {
                        Artisan::call('queue:forget', ['id' => $record->uuid]);

                        app(LogActivityAction::class)->execute('failed_job.delete', $record, [
                            'uuid' => (string) $record->uuid,
                            'job' => $record->job_name,
                        ]);

                        Notification::make()
                            ->success()
                            ->title('Job deleted')
                            ->send();
                    }),
            ])
            ->toolbarActions([
                BulkAction::make('retryBulk')
                    ->label('Retry selected')
                    ->icon('heroicon-o-arrow-path')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(static function (Collection $records): void {
                        $uuids = $records->pluck('uuid')->all();
                        Artisan::call('queue:retry', ['id' => $uuids]);

                        Notification::make()
                            ->success()
                            ->title(sprintf('%d job re-queued', count($uuids)))
                            ->send();
                    }),
            ])
            ->defaultSort('failed_at', 'desc');
    }
}
