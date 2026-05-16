<?php

declare(strict_types=1);

namespace App\Filament\Resources\Organizations\Tables;

use App\Actions\SuperAdmin\LogActivityAction;
use App\Domain\Organization\Enums\PlanTier;
use App\Models\Organization;
use Exception;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;

class OrganizationsTable
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

                TextColumn::make('slug')
                    ->label('Slug')
                    ->searchable(),

                TextColumn::make('owner_name')
                    ->label('Owner')
                    ->state(static function (Organization $record): ?string {
                        return DB::table('organization_members')
                            ->join('users', 'users.id', '=', 'organization_members.user_id')
                            ->where('organization_members.organization_id', $record->id)
                            ->where('organization_members.role', 'organization_owner')
                            ->orderBy('organization_members.id')
                            ->value('users.name');
                    }),

                TextColumn::make('member_count')
                    ->label('Members')
                    ->state(static fn (Organization $record): int => (int) DB::table('organization_members')
                        ->where('organization_id', $record->id)
                        ->count()),

                TextColumn::make('active_projects')
                    ->label('Active Projects')
                    ->state(static fn (Organization $record): int => (int) DB::table('projects')
                        ->where('organization_id', $record->id)
                        ->whereNotIn('status', ['archived', 'completed'])
                        ->count()),

                TextColumn::make('plan_tier')
                    ->label('Plan Tier')
                    ->badge()
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge(),

                TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('plan_tier')
                    ->label('Plan Tier')
                    ->options(PlanTier::options()),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('forceDelete')
                    ->label('Force Delete')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Force delete organization')
                    ->modalDescription('Permanently deletes the organization and all linked workspace data. This action cannot be undone.')
                    ->schema([
                        TextInput::make('confirmation')
                            ->label('Type the organization name to confirm')
                            ->required(),
                    ])
                    ->action(static function (Organization $record, array $data): void {
                        if (($data['confirmation'] ?? null) !== $record->name) {
                            Notification::make()
                                ->danger()
                                ->title('Confirmation mismatch')
                                ->body('Type the organization name exactly to confirm.')
                                ->send();

                            throw new Exception('Confirmation mismatch.');
                        }

                        $memberCount = (int) DB::table('organization_members')
                            ->where('organization_id', $record->id)
                            ->count();

                        app(LogActivityAction::class)->execute('org.force_delete', $record, [
                            'name' => (string) $record->name,
                            'member_count' => $memberCount,
                        ]);

                        $record->delete();

                        Notification::make()
                            ->success()
                            ->title('Organization deleted')
                            ->body(sprintf('"%s" has been permanently removed.', $record->name))
                            ->send();
                    }),
            ])
            ->toolbarActions([])
            ->defaultSort('created_at', 'desc');
    }
}
