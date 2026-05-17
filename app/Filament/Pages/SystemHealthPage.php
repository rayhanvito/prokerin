<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Actions\Admin\GetPlatformHealthAction;
use App\Filament\SuperAdminGate;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

final class SystemHealthPage extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSignal;

    protected static ?string $navigationLabel = 'System Health';

    protected static string|UnitEnum|null $navigationGroup = 'Insights';

    protected static ?int $navigationSort = 10;

    protected static ?string $slug = 'system-health';

    protected static ?string $title = 'System Health';

    protected string $view = 'filament.pages.system-health-page';

    /**
     * @var array<string, array{status: string, detail: string}>
     */
    public array $health = [];

    public function mount(GetPlatformHealthAction $healthAction): void
    {
        $this->health = $healthAction->execute();
    }

    public static function canAccess(): bool
    {
        return SuperAdminGate::canAccess();
    }

    public function refreshHealth(GetPlatformHealthAction $healthAction): void
    {
        $this->health = $healthAction->execute();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('refreshHealth')
                ->label('Refresh')
                ->icon('heroicon-o-arrow-path')
                ->action('refreshHealth'),
        ];
    }
}
