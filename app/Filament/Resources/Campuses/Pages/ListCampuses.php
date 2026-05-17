<?php

declare(strict_types=1);

namespace App\Filament\Resources\Campuses\Pages;

use App\Filament\Resources\Campuses\CampusResource;
use Filament\Resources\Pages\ListRecords;

final class ListCampuses extends ListRecords
{
    protected static string $resource = CampusResource::class;
}
