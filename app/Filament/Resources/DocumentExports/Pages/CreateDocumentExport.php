<?php

declare(strict_types=1);

namespace App\Filament\Resources\DocumentExports\Pages;

use App\Filament\Resources\DocumentExports\DocumentExportResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDocumentExport extends CreateRecord
{
    protected static string $resource = DocumentExportResource::class;
}
