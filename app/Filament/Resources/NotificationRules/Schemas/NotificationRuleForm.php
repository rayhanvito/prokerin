<?php

declare(strict_types=1);

namespace App\Filament\Resources\NotificationRules\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class NotificationRuleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Rule')
                    ->schema([
                        TextInput::make('event')
                            ->label('Event Type')
                            ->disabled()
                            ->dehydrated(false),

                        TextInput::make('label')
                            ->label('Label')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('audience')
                            ->label('Audience')
                            ->required()
                            ->maxLength(255),

                        Select::make('channels')
                            ->label('Channels')
                            ->multiple()
                            ->options([
                                'email' => 'Email',
                                'in_app' => 'In-App',
                                'whatsapp' => 'WhatsApp',
                            ])
                            ->required(),

                        TextInput::make('trigger')
                            ->label('Trigger')
                            ->required()
                            ->maxLength(255),

                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'enabled' => 'Enabled',
                                'disabled' => 'Disabled',
                                'planned' => 'Planned',
                            ])
                            ->required(),
                    ])
                    ->columns(2),
            ]);
    }
}
