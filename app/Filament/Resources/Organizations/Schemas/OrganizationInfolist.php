<?php

declare(strict_types=1);

namespace App\Filament\Resources\Organizations\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrganizationInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Organization')
                    ->schema([
                        TextEntry::make('id')->label('ID'),
                        TextEntry::make('name')->label('Name'),
                        TextEntry::make('slug')->label('Slug'),
                        TextEntry::make('plan_tier')->label('Plan Tier')->badge(),
                        TextEntry::make('status')->label('Status')->badge(),
                        TextEntry::make('logo_path')
                            ->label('Logo Path')
                            ->placeholder('-'),
                        TextEntry::make('created_at')->label('Created At')->dateTime(),
                        TextEntry::make('updated_at')->label('Last Updated')->dateTime(),
                    ])
                    ->columns(2),

                Section::make('Internal Notes')
                    ->schema([
                        TextEntry::make('internal_notes')
                            ->label('Internal Notes')
                            ->placeholder('No internal notes'),
                    ]),
            ]);
    }
}
