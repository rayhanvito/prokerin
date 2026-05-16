<?php

declare(strict_types=1);

namespace App\DTOs\Dashboard;

enum DashboardTone: string
{
    case Primary = 'primary';
    case Success = 'success';
    case Warning = 'warning';
    case Danger = 'danger';
    case Default = 'default';
}
