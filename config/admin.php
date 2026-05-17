<?php

declare(strict_types=1);

return [
    'session_idle_minutes' => (int) env('ADMIN_SESSION_IDLE_MINUTES', 30),
    'allowed_ips' => array_values(array_filter(array_map('trim', explode(',', (string) env('ADMIN_ALLOWED_IPS', ''))))),
    'reauth_valid_minutes' => (int) env('ADMIN_REAUTH_VALID_MINUTES', 15),
    'two_factor_required' => (bool) env('ADMIN_2FA_REQUIRED', false),
    'audit_retention_days' => (int) env('ADMIN_AUDIT_RETENTION_DAYS', 365),
    'failed_jobs_retention_days' => (int) env('ADMIN_FAILED_JOBS_RETENTION_DAYS', 90),
];
