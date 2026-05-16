<?php

declare(strict_types=1);

namespace App\Filament\Resources\DocumentExports\Pages;

use App\Filament\Resources\DocumentExports\DocumentExportResource;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditDocumentExport extends EditRecord
{
    protected static string $resource = DocumentExportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
        ];
    }
}
