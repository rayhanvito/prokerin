<?php

declare(strict_types=1);

namespace App\Domain\Organization\Enums;

enum OrganizationMode: string
{
    case Organization = 'organization';
    case Kepanitiaan = 'kepanitiaan';
}
