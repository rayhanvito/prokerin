<?php

declare(strict_types=1);

namespace App\Filament\Resources\Projects\Tables;

use App\Models\Organization;
use App\Models\Project;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ProjectsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('name')
                    ->label('Name')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('organization.name')
                    ->label('Organization')
                    ->searchable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->sortable(),

                TextColumn::make('progress')
                    ->label('Progress')
                    ->state(static fn (Project $record): string => sprintf('%d%%', (int) $record->progress))
                    ->sortable(),

                TextColumn::make('projectLead.name')
                    ->label('Lead')
                    ->placeholder('-'),

                TextColumn::make('starts_at')
                    ->label('Start Date')
                    ->date()
                    ->sortable(),

                TextColumn::make('ends_at')
                    ->label('End Date')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'planning' => 'Planning',
                        'proposal_review' => 'Proposal Review',
                        'rab_approval' => 'RAB Approval',
                        'active' => 'Active',
                        'completed' => 'Completed',
                        'archived' => 'Archived',
                    ]),

                SelectFilter::make('organization_id')
                    ->label('Organization')
                    ->options(static fn (): array => Organization::query()
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->all()),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([])
            ->defaultSort('created_at', 'desc');
    }
}
