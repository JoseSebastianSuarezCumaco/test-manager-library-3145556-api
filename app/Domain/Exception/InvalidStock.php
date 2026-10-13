<?php

declare(strict_types=1);

namespace App\Domain\Exception;

final class InvalidStock extends DomainRuleViolation
{
    public static function outOfRange(): self
    {
        return new self('Los ejemplares disponibles deben estar entre 0 y el total.');
    }

    public static function availableBelowZero(): self
    {
        return new self('El nuevo total deja los ejemplares disponibles por debajo de cero.');
    }
}