<?php

declare(strict_types=1);

namespace App\Domain\Document;

enum DocumentVisibility: string
{
    case Private = 'private';
    case Restricted = 'restricted';
    case Committee = 'committee';
    case Public = 'public';

    public function requiresSignedUrl(): bool
    {
        return ! in_array($this, [self::Committee, self::Public], true);
    }
}
