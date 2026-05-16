<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Organization;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class RecentOrganizationsTable extends BaseWidget
{
    protected static ?string $heading = 'Recent Organizations';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(static fn (): Builder => Organization::query()->orderByDesc('created_at')->limit(10))
            ->columns([
                TextColumn::make('name')
                    ->label('Name')
                    ->searchable(),

                TextColumn::make('slug')
                    ->label('Slug')
                    ->toggleable(),

                TextColumn::make('plan_tier')
                    ->label('Plan Tier')
                    ->badge(),

                TextColumn::make('member_count')
                    ->label('Members')
                    ->state(static fn (Organization $record): int => (int) DB::table('organization_members')
                        ->where('organization_id', $record->id)
                        ->count()),

                TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated(false);
    }
}
