<?php

declare(strict_types=1);

namespace App\Domain\Organization\Enums;

enum PlanTier: string
{
    case Free = 'free';
    case Starter = 'starter';
    case Pro = 'pro';
    case Campus = 'campus';

    public function label(): string
    {
        return match ($this) {
            self::Free => 'Free',
            self::Starter => 'Starter',
            self::Pro => 'Pro',
            self::Campus => 'Campus',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        $options = [];

        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }
}
