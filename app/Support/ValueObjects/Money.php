<?php

declare(strict_types=1);

namespace App\Support\ValueObjects;

use InvalidArgumentException;

final readonly class Money
{
    public function __construct(
        public int $amount,
        public string $currency = 'IDR',
    ) {
        if ($amount < 0) {
            throw new InvalidArgumentException('Money amount cannot be negative.');
        }
    }

    public static function rupiah(int $amount): self
    {
        return new self($amount, 'IDR');
    }

    public function add(self $money): self
    {
        $this->ensureSameCurrency($money);

        return new self($this->amount + $money->amount, $this->currency);
    }

    public function subtract(self $money): self
    {
        $this->ensureSameCurrency($money);

        return new self(max(0, $this->amount - $money->amount), $this->currency);
    }

    public function formatted(): string
    {
        if ($this->currency === 'IDR') {
            return 'Rp'.number_format($this->amount, 0, ',', '.');
        }

        return $this->currency.' '.number_format($this->amount);
    }

    private function ensureSameCurrency(self $money): void
    {
        if ($this->currency !== $money->currency) {
            throw new InvalidArgumentException('Money currency must match.');
        }
    }
}
