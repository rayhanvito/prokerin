<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Models\Project;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

final class ActiveProkerByPhase extends BaseWidget
{
    protected static ?string $heading = 'Proker by Phase';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(static fn (): Builder => Project::query()
                ->select('status', DB::raw('count(*) as aggregate_count'))
                ->groupBy('status')
                ->orderByDesc('aggregate_count'))
            ->columns([
                TextColumn::make('status')->badge(),
                TextColumn::make('aggregate_count')->label('Projects')->numeric(),
            ])
            ->paginated(false);
    }
}
