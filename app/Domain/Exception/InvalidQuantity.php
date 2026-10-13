<?php

declare(strict_types=1);

namespace App\Domain\Exception;

final class InvalidQuantity extends DomainRuleViolation
{
    public static function mustBePositive(int $value): self
    {
        return new self('La cantidad debe ser mayor que cero.');
    }
}