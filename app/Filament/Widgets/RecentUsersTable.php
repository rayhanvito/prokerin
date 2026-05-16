<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;

class RecentUsersTable extends BaseWidget
{
    protected static ?string $heading = 'Recent Users';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(static fn (): Builder => User::query()->orderByDesc('created_at')->limit(10))
            ->columns([
                TextColumn::make('name')
                    ->label('Name')
                    ->searchable(),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),

                IconColumn::make('email_verified_at')
                    ->label('Verified')
                    ->boolean(),

                TextColumn::make('roles.name')
                    ->label('Roles')
                    ->badge()
                    ->separator(','),

                TextColumn::make('created_at')
                    ->label('Registered At')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated(false);
    }
}
