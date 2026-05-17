<?php

declare(strict_types=1);

namespace App\Filament\Resources\FailedJobs\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class FailedJobInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Job Failure Detail')
                    ->schema([
                        TextEntry::make('uuid')->label('UUID'),
                        TextEntry::make('connection')->label('Connection'),
                        TextEntry::make('queue')->label('Queue'),
                        TextEntry::make('failed_at')->label('Failed At')->dateTime(),
                        TextEntry::make('exception')
                            ->label('Exception (full trace)')
                            ->columnSpanFull()
                            ->html()
                            ->state(static fn ($record): string => '<pre style="white-space:pre-wrap;font-family:monospace;font-size:12px">'.e((string) $record->exception).'</pre>'),
                        TextEntry::make('payload')
                            ->label('Payload')
                            ->columnSpanFull()
                            ->state(static fn ($record): string => json_encode(json_decode((string) $record->payload, true), JSON_PRETTY_PRINT) ?: ''),
                    ])
                    ->columns(2),
            ]);
    }
}
