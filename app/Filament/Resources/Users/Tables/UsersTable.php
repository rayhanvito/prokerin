<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Tables;

use App\Actions\SuperAdmin\LogActivityAction;
use App\Models\User;
use Exception;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class UsersTable
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

                TextColumn::make('email')
                    ->label('Email')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('roles.name')
                    ->label('Roles')
                    ->badge()
                    ->separator(','),

                TextColumn::make('organizations_count')
                    ->label('Organizations')
                    ->state(static fn (User $record): int => (int) DB::table('organization_members')
                        ->where('user_id', $record->id)
                        ->count()),

                IconColumn::make('email_verified_at')
                    ->label('Email Verified')
                    ->boolean()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->label('Registered At')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('roles')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload(),

                Filter::make('email_verified')
                    ->label('Email Verified')
                    ->query(static fn (Builder $query): Builder => $query->whereNotNull('email_verified_at')),

                Filter::make('email_not_verified')
                    ->label('Not Verified')
                    ->query(static fn (Builder $query): Builder => $query->whereNull('email_verified_at')),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('impersonate')
                    ->label('Impersonate')
                    ->icon('heroicon-o-user-circle')
                    ->color('warning')
                    ->visible(static fn (User $record): bool => self::canImpersonate($record))
                    ->action(static function (User $record): void {
                        $current = auth()->user();

                        if (! $current instanceof User || ! $current->canImpersonate()) {
                            Notification::make()
                                ->danger()
                                ->title('Not allowed')
                                ->body('Only super admins can impersonate users.')
                                ->send();

                            return;
                        }

                        if (! $record->canBeImpersonated()) {
                            Notification::make()
                                ->danger()
                                ->title('Cannot impersonate')
                                ->body('This user cannot be impersonated.')
                                ->send();

                            return;
                        }

                        app(LogActivityAction::class)->execute('impersonate.start', $record, [
                            'target_user_id' => $record->id,
                            'target_user_email' => $record->email,
                        ]);

                        $current->impersonate($record);

                        redirect()->to(route('dashboard'));
                    }),
                DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('Delete user')
                    ->modalDescription('This action cannot be undone. The user will be permanently removed.')
                    ->before(static function (User $record): void {
                        self::guardDelete($record);
                    })
                    ->after(static function (User $record): void {
                        app(LogActivityAction::class)->execute('user.delete', $record, [
                            'email' => (string) $record->email,
                            'name' => (string) $record->name,
                        ]);
                    }),
            ])
            ->toolbarActions([])
            ->defaultSort('created_at', 'desc');
    }

    private static function canImpersonate(User $record): bool
    {
        $current = auth()->user();

        if (! $current instanceof User) {
            return false;
        }

        if (! $current->canImpersonate()) {
            return false;
        }

        if ($current->getKey() === $record->getKey()) {
            return false;
        }

        return $record->canBeImpersonated();
    }

    private static function guardDelete(User $record): void
    {
        $current = auth()->user();

        if ($current instanceof User && $current->getKey() === $record->getKey()) {
            Notification::make()
                ->danger()
                ->title('Cannot delete')
                ->body('You cannot delete your own account.')
                ->send();

            throw new Exception('Cannot delete your own account.');
        }

        $superAdminRole = Role::query()->where('name', 'super_admin')->first();

        if ($superAdminRole !== null && $record->hasRole('super_admin')) {
            Notification::make()
                ->danger()
                ->title('Cannot delete')
                ->body('Super admin users must be removed via Artisan, not the panel.')
                ->send();

            throw new Exception('Cannot delete a super admin via UI.');
        }

        $soleOwnerOrgs = DB::table('organization_members as om')
            ->where('om.user_id', $record->id)
            ->where('om.role', 'organization_owner')
            ->whereNotExists(static function ($subQuery) {
                $subQuery->select(DB::raw(1))
                    ->from('organization_members as other')
                    ->whereColumn('other.organization_id', 'om.organization_id')
                    ->where('other.role', 'organization_owner')
                    ->whereColumn('other.user_id', '!=', 'om.user_id');
            })
            ->exists();

        if ($soleOwnerOrgs) {
            Notification::make()
                ->danger()
                ->title('Cannot delete')
                ->body('This user is the sole owner of one or more organizations. Reassign ownership first.')
                ->send();

            throw new Exception('User is sole organization owner.');
        }
    }
}
