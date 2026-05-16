<?php

declare(strict_types=1);

namespace App\Filament\Resources\Projects\Schemas;

use App\Models\Project;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\DB;

class ProjectInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Project')
                    ->schema([
                        TextEntry::make('name')->label('Name'),
                        TextEntry::make('organization.name')->label('Organization'),
                        TextEntry::make('status')->label('Status')->badge(),
                        TextEntry::make('progress')
                            ->label('Progress')
                            ->state(static fn (Project $record): string => sprintf('%d%%', (int) $record->progress)),
                        TextEntry::make('projectLead.name')->label('Project Lead')->placeholder('-'),
                        TextEntry::make('starts_at')->label('Start Date')->date(),
                        TextEntry::make('ends_at')->label('End Date')->date(),
                        TextEntry::make('description')
                            ->label('Description')
                            ->columnSpanFull()
                            ->placeholder('-'),
                    ])
                    ->columns(2),

                Section::make('Summary')
                    ->schema([
                        TextEntry::make('task_total')
                            ->label('Total Tasks')
                            ->state(static fn (Project $record): int => (int) DB::table('project_tasks')
                                ->where('project_id', $record->id)
                                ->count()),

                        TextEntry::make('task_done')
                            ->label('Completed Tasks')
                            ->state(static fn (Project $record): int => (int) DB::table('project_tasks')
                                ->where('project_id', $record->id)
                                ->where('status', 'done')
                                ->count()),

                        TextEntry::make('budget_total')
                            ->label('Planned Budget')
                            ->state(static fn (Project $record): string => 'Rp '.number_format((float) DB::table('budget_lines')
                                ->where('project_id', $record->id)
                                ->sum('planned_amount'), 0, ',', '.')),

                        TextEntry::make('proposal_status')
                            ->label('Proposal Status')
                            ->state(static fn (Project $record): string => (string) (DB::table('proposal_drafts')
                                ->where('project_id', $record->id)
                                ->value('status') ?? '-')),

                        TextEntry::make('lpj_progress')
                            ->label('LPJ Progress')
                            ->state(static function (Project $record): string {
                                $total = (int) DB::table('lpj_checklist_items')
                                    ->where('project_id', $record->id)
                                    ->count();
                                $done = (int) DB::table('lpj_checklist_items')
                                    ->where('project_id', $record->id)
                                    ->where('is_complete', true)
                                    ->count();

                                return $total === 0
                                    ? '-'
                                    : sprintf('%d / %d', $done, $total);
                            }),
                    ])
                    ->columns(2),
            ]);
    }
}
