<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

use App\Domain\Exception\InvalidQuantity;

final class Quantity
{
    private function __construct(private readonly int $value)
    {
    }

    public static function of(int $value): self
    {
        if ($value <= 0) {
            throw InvalidQuantity::mustBePositive($value);
        }

        return new self($value);
    }

    public function value(): int
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}