<?php

declare(strict_types=1);

namespace App\Filament\Resources\OrganizationInvitations;

use App\Filament\Resources\OrganizationInvitations\Pages\ListOrganizationInvitations;
use App\Filament\SuperAdminGate;
use App\Models\OrganizationInvitation;
use BackedEnum;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

final class OrganizationInvitationResource extends Resource
{
    protected static ?string $model = OrganizationInvitation::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static string|UnitEnum|null $navigationGroup = 'Platform';

    protected static ?string $navigationLabel = 'Invitations';

    protected static ?int $navigationSort = 50;

    public static function canViewAny(): bool
    {
        return SuperAdminGate::canAccess();
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('email')->searchable(),
                TextColumn::make('organization.name')->label('Organization')->searchable(),
                TextColumn::make('role')->badge(),
                TextColumn::make('status')->badge(),
                TextColumn::make('expires_at')->dateTime('Y-m-d H:i')->sortable(),
                TextColumn::make('created_at')->dateTime('Y-m-d H:i')->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->options([
                    'pending' => 'Pending',
                    'accepted' => 'Accepted',
                    'declined' => 'Declined',
                    'expired' => 'Expired',
                ]),
                SelectFilter::make('role')
                    ->options(static fn (): array => OrganizationInvitation::query()->select('role')->distinct()->pluck('role', 'role')->all()),
            ])
            ->recordActions([ViewAction::make()])
            ->defaultSort('created_at', 'desc')
            ->emptyStateIcon('heroicon-o-envelope')
            ->emptyStateHeading('Belum ada invitation')
            ->emptyStateDescription('Invitation organisasi akan muncul di sini.');
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['email', 'organization.name'];
    }

    public static function getPages(): array
    {
        return ['index' => ListOrganizationInvitations::route('/')];
    }
}
