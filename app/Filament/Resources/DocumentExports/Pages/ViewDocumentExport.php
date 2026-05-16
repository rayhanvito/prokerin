<?php

declare(strict_types=1);

namespace App\Filament\Resources\DocumentExports\Pages;

use App\Filament\Resources\DocumentExports\DocumentExportResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewDocumentExport extends ViewRecord
{
    protected static string $resource = DocumentExportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
