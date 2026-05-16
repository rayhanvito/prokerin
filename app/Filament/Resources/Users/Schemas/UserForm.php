<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Account')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        TextInput::make('password')
                            ->password()
                            ->revealable()
                            ->dehydrated(static fn ($state): bool => filled($state))
                            ->required(static fn (?string $operation): bool => $operation === 'create')
                            ->helperText('Leave blank to keep existing password (on edit).'),

                        DateTimePicker::make('email_verified_at')
                            ->label('Email Verified At')
                            ->helperText('Set to mark the email as verified, clear to revoke verification.'),
                    ])
                    ->columns(2),

                Section::make('Roles')
                    ->description('System roles. Super admin role can only be assigned via Artisan.')
                    ->schema([
                        Select::make('roles')
                            ->relationship('roles', 'name', static fn ($query) => $query->where('name', '!=', 'super_admin'))
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->helperText('super_admin role is not assignable via UI.'),
                    ]),
            ]);
    }
}
