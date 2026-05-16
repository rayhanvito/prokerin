<?php

declare(strict_types=1);

namespace App\Filament\Resources\Users\Pages;

use App\Actions\SuperAdmin\LogActivityAction;
use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    /** @var array<int, int> */
    protected array $rolesBefore = [];

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
        ];
    }

    protected function beforeSave(): void
    {
        /** @var User $record */
        $record = $this->record;

        $this->rolesBefore = $record->roles()->pluck('id')->all();
    }

    protected function afterSave(): void
    {
        /** @var User $record */
        $record = $this->record;

        $rolesAfter = $record->roles()->pluck('id')->all();

        if ($this->rolesBefore !== $rolesAfter) {
            app(LogActivityAction::class)->execute('user.role.change', $record, [
                'before' => array_values($this->rolesBefore),
                'after' => array_values($rolesAfter),
            ]);
        }
    }
}
