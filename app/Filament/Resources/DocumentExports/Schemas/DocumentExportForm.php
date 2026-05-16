<?php

declare(strict_types=1);

namespace App\Filament\Resources\DocumentExports\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class DocumentExportForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('organization_id')
                    ->required()
                    ->numeric(),
                TextInput::make('project_id')
                    ->numeric(),
                TextInput::make('requested_by_user_id')
                    ->numeric(),
                TextInput::make('document_title')
                    ->required(),
                TextInput::make('document_type')
                    ->required(),
                TextInput::make('format')
                    ->required(),
                TextInput::make('queue_name')
                    ->required(),
                TextInput::make('engine')
                    ->required(),
                TextInput::make('storage_disk')
                    ->required(),
                TextInput::make('output_path')
                    ->required(),
                TextInput::make('status')
                    ->required()
                    ->default('queued'),
            ]);
    }
}
