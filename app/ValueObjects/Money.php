<?php

namespace App\ValueObjects;

use InvalidArgumentException;

final class Money
{
    private function __construct(
        public readonly int $minorUnits,
        public readonly string $currency = 'EGP'
    ) {}

    public static function fromMinor(int $minorUnits, string $currency = 'EGP'): self
    {
        return new self($minorUnits, strtoupper($currency));
    }

    public static function fromMajor(float|int $majorUnits, string $currency = 'EGP'): self
    {
        return new self((int) round($majorUnits * 100), strtoupper($currency));
    }

    public function add(self $other): self
    {
        $this->assertSameCurrency($other);
        return new self($this->minorUnits + $other->minorUnits, $this->currency);
    }

    public function subtract(self $other): self
    {
        $this->assertSameCurrency($other);
        return new self($this->minorUnits - $other->minorUnits, $this->currency);
    }

    public function multiply(float|int $multiplier): self
    {
        return new self((int) round($this->minorUnits * $multiplier), $this->currency);
    }

    public function toMajor(): float
    {
        return round($this->minorUnits / 100, 2);
    }

    public function format(): string
    {
        return sprintf('%.2f %s', $this->toMajor(), $this->currency);
    }

    private function assertSameCurrency(self $other): void
    {
        if ($this->currency !== $other->currency) {
            throw new InvalidArgumentException("Cannot operate on different currencies: {$this->currency} and {$other->currency}.");
        }
    }
}
