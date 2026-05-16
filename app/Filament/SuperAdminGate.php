<?php

declare(strict_types=1);

namespace App\Filament;

use App\Models\User;

final class SuperAdminGate
{
    public static function canAccess(?User $user = null): bool
    {
        $user = $user ?? auth()->user();

        if (! $user instanceof User) {
            return false;
        }

        return $user->hasRole('super_admin');
    }
}
