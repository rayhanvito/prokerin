<?php

declare(strict_types=1);

namespace App\Filament\Resources\FeatureFlags\Tables;

use App\Domain\Organization\Enums\PlanTier;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class FeatureFlagsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('key')
                    ->label('Key')
                    ->searchable()
                    ->sortable(),

                IconColumn::make('is_enabled_globally')
                    ->label('Global')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('enabled_plan_tiers')
                    ->label('Plan Tiers')
                    ->badge()
                    ->separator(',')
                    ->placeholder('None'),

                TextColumn::make('enabled_organization_ids')
                    ->label('Org Targets')
                    ->state(static fn ($record): int => count($record->enabled_organization_ids ?? []))
                    ->suffix(' org(s)'),

                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->filters([
                Filter::make('enabled_globally')
                    ->label('Enabled globally')
                    ->query(static fn (Builder $query): Builder => $query->where('is_enabled_globally', true)),

                SelectFilter::make('enabled_plan_tiers')
                    ->label('Plan tier')
                    ->options(PlanTier::options())
                    ->query(static fn (Builder $query, array $data): Builder => filled($data['value'] ?? null)
                        ? $query->whereJsonContains('enabled_plan_tiers', $data['value'])
                        : $query),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('Delete feature flag')
                    ->modalDescription('This removes the flag configuration immediately. Code using this key will read it as disabled.'),
            ])
            ->defaultSort('updated_at', 'desc');
    }
}
