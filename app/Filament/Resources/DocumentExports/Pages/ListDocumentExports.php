<?php

declare(strict_types=1);

namespace App\Filament\Resources\DocumentExports\Pages;

use App\Filament\Resources\DocumentExports\DocumentExportResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDocumentExports extends ListRecords
{
    protected static string $resource = DocumentExportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
