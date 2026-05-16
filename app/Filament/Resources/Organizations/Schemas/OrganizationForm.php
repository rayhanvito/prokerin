<?php

declare(strict_types=1);

namespace App\Filament\Resources\Organizations\Schemas;

use App\Domain\Organization\Enums\PlanTier;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrganizationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Organization')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),

                        TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->helperText('Globally unique identifier used in URLs.'),

                        TextInput::make('logo_path')
                            ->maxLength(255),

                        Select::make('status')
                            ->required()
                            ->default('active')
                            ->options([
                                'active' => 'Active',
                                'archived' => 'Archived',
                            ]),

                        Select::make('plan_tier')
                            ->label('Plan Tier')
                            ->required()
                            ->default(PlanTier::Free->value)
                            ->options(PlanTier::options()),
                    ])
                    ->columns(2),

                Section::make('Internal Notes')
                    ->description('Visible only to platform super admins; never shown to organization users.')
                    ->schema([
                        Textarea::make('internal_notes')
                            ->label('Internal Notes')
                            ->rows(4),
                    ]),
            ]);
    }
}
