<?php

declare(strict_types=1);

namespace App\Filament\Resources\FeatureFlags\Schemas;

use App\Domain\Organization\Enums\PlanTier;
use App\Models\FeatureFlag;
use App\Models\Organization;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rule;

class FeatureFlagForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Feature')
                    ->schema([
                        TextInput::make('key')
                            ->label('Key')
                            ->required()
                            ->maxLength(255)
                            ->rules(static fn (?FeatureFlag $record): array => [
                                'regex:/^[a-z0-9_]+$/',
                                Rule::unique('feature_flags', 'key')->ignore($record),
                            ])
                            ->helperText('Use lowercase snake_case, for example m22_payment.'),

                        Toggle::make('is_enabled_globally')
                            ->label('Enabled globally')
                            ->default(false),

                        Textarea::make('description')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Targeting')
                    ->description('A flag is enabled when global is on, or the active organization matches one of these targets.')
                    ->schema([
                        Select::make('enabled_organization_ids')
                            ->label('Organizations')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->options(static fn (): array => Organization::query()
                                ->orderBy('name')
                                ->pluck('name', 'id')
                                ->all()),

                        Select::make('enabled_plan_tiers')
                            ->label('Plan tiers')
                            ->multiple()
                            ->options(PlanTier::options()),
                    ])
                    ->columns(2),
            ]);
    }
}
