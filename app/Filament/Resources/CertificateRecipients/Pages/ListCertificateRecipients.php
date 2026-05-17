<?php

declare(strict_types=1);

namespace App\Filament\Resources\CertificateRecipients\Pages;

use App\Filament\Resources\CertificateRecipients\CertificateRecipientResource;
use Filament\Resources\Pages\ListRecords;

final class ListCertificateRecipients extends ListRecords
{
    protected static string $resource = CertificateRecipientResource::class;
}
