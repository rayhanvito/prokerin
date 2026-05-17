<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Actions\Admin\GetOnboardingChecklistAction;
use App\Filament\SuperAdminGate;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

final class OnboardingChecklistPage extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static ?string $navigationLabel = 'Onboarding Checklist';

    protected static string|UnitEnum|null $navigationGroup = 'Operations';

    protected static ?int $navigationSort = 80;

    protected static ?string $slug = 'onboarding-checklist';

    protected static ?string $title = 'Onboarding Checklist';

    protected string $view = 'filament.pages.onboarding-checklist-page';

    /**
     * @var list<array{id: int, name: string, slug: string, plan_tier: string, created_at: string, checklist: array<string, bool>, completed_count: int, total_count: int}>
     */
    public array $organizations = [];

    public function mount(GetOnboardingChecklistAction $checklistAction): void
    {
        $this->organizations = $checklistAction->execute();
    }

    public static function canAccess(): bool
    {
        return SuperAdminGate::canAccess();
    }

    public function refreshChecklist(GetOnboardingChecklistAction $checklistAction): void
    {
        $this->organizations = $checklistAction->execute();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('refreshChecklist')
                ->label('Refresh')
                ->icon('heroicon-o-arrow-path')
                ->action('refreshChecklist'),
        ];
    }
}
