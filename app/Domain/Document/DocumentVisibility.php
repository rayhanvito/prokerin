<?php

declare(strict_types=1);

namespace App\Domain\Document;

enum DocumentVisibility: string
{
    case Private = 'private';
    case Restricted = 'restricted';
    case Committee = 'committee';

    public function requiresSignedUrl(): bool
    {
        return $this !== self::Committee;
    }
}
