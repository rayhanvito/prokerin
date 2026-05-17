<?php

declare(strict_types=1);

namespace App\Filament\Resources\Projects\Tables;

use App\Models\Organization;
use App\Models\Project;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\HtmlString;

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

                Filter::make('stuck_proposal_review')
                    ->label('Stuck in proposal review >14 days')
                    ->query(static fn (Builder $query): Builder => $query
                        ->where('status', 'proposal_review')
                        ->where('updated_at', '<=', now()->subDays(14))),

                Filter::make('completed_without_lpj')
                    ->label('Completed but LPJ incomplete')
                    ->query(static fn (Builder $query): Builder => $query
                        ->where('status', 'completed')
                        ->whereExists(static function ($subQuery): void {
                            $subQuery->select(DB::raw(1))
                                ->from('lpj_checklist_items')
                                ->whereColumn('lpj_checklist_items.project_id', 'projects.id')
                                ->where('lpj_checklist_items.is_required', true)
                                ->where('lpj_checklist_items.is_complete', false);
                        })),
            ])
            ->recordActions([
                ViewAction::make(),
                Action::make('healthReport')
                    ->label('Health')
                    ->icon('heroicon-o-heart')
                    ->modalHeading(static fn (Project $record): string => "Health Report: {$record->name}")
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->modalContent(static fn (Project $record): HtmlString => self::healthReport($record)),
            ])
            ->toolbarActions([])
            ->defaultSort('created_at', 'desc');
    }

    private static function healthReport(Project $project): HtmlString
    {
        $taskTotal = (int) DB::table('project_tasks')->where('project_id', $project->id)->count();
        $taskDone = (int) DB::table('project_tasks')->where('project_id', $project->id)->where('status', 'done')->count();
        $planned = (int) DB::table('budget_lines')->where('project_id', $project->id)->sum('planned_amount');
        $realized = (int) DB::table('budget_lines')->where('project_id', $project->id)->sum('realized_amount');
        $proposalStatus = (string) (DB::table('proposal_drafts')->where('project_id', $project->id)->latest('id')->value('status') ?? 'none');
        $lpjRequired = (int) DB::table('lpj_checklist_items')->where('project_id', $project->id)->where('is_required', true)->count();
        $lpjComplete = (int) DB::table('lpj_checklist_items')->where('project_id', $project->id)->where('is_required', true)->where('is_complete', true)->count();
        $lpjReadiness = $lpjRequired === 0 ? 0 : (int) round(($lpjComplete / $lpjRequired) * 100);
        $lastTaskUpdate = DB::table('project_tasks')->where('project_id', $project->id)->max('updated_at');

        $html = sprintf(
            '<dl class="grid grid-cols-1 gap-3 text-sm md:grid-cols-2">
                <div><dt class="font-semibold text-gray-950">Tasks</dt><dd>%d / %d done</dd></div>
                <div><dt class="font-semibold text-gray-950">Budget</dt><dd>Rp%s planned / Rp%s realized</dd></div>
                <div><dt class="font-semibold text-gray-950">Proposal</dt><dd>%s</dd></div>
                <div><dt class="font-semibold text-gray-950">LPJ readiness</dt><dd>%d%% (%d / %d required)</dd></div>
                <div><dt class="font-semibold text-gray-950">Last task update</dt><dd>%s</dd></div>
                <div><dt class="font-semibold text-gray-950">Progress</dt><dd>%d%%</dd></div>
            </dl>',
            $taskDone,
            $taskTotal,
            number_format($planned, 0, ',', '.'),
            number_format($realized, 0, ',', '.'),
            e($proposalStatus),
            $lpjReadiness,
            $lpjComplete,
            $lpjRequired,
            e((string) ($lastTaskUpdate ?? '-')),
            (int) $project->progress,
        );

        return new HtmlString($html);
    }
}
