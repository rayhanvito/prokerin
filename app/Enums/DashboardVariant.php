<?php

declare(strict_types=1);

namespace App\Enums;

enum DashboardVariant: string
{
    case Pimpinan = 'pimpinan';
    case Sekretaris = 'sekretaris';
    case Bendahara = 'bendahara';
    case Operasional = 'operasional';
    case Member = 'member';
    case Viewer = 'viewer';
}
