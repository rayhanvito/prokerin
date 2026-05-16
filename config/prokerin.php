<?php

declare(strict_types=1);

return [
    'filament' => [
        'admin_path' => env('FILAMENT_ADMIN_PATH', 'internal-admin'),
        'brand_name' => env('FILAMENT_BRAND_NAME', 'Prokerin Admin'),
    ],

    'impersonate' => [
        'max_duration_hours' => (int) env('IMPERSONATE_MAX_DURATION_HOURS', 2),
    ],
];
