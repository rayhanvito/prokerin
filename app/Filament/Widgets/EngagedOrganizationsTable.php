<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Organization;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

final class EngagedOrganizationsTable extends BaseWidget
{
    protected static ?string $heading = 'Top Engaged Organizations';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(static fn (): Builder => Organization::query()
                ->select('organizations.*')
                ->withCount(['projects'])
                ->addSelect([
                    'member_count' => DB::table('organization_members')
                        ->selectRaw('count(*)')
                        ->whereColumn('organization_members.organization_id', 'organizations.id'),
                ])
                ->orderByDesc('updated_at')
                ->limit(10))
            ->columns([
                TextColumn::make('name')->searchable(),
                TextColumn::make('plan_tier')->badge(),
                TextColumn::make('member_count')->label('Members')->numeric(),
                TextColumn::make('projects_count')->label('Projects')->numeric(),
                TextColumn::make('updated_at')->label('Last Activity')->dateTime('Y-m-d H:i'),
            ])
            ->paginated(false);
    }
}
