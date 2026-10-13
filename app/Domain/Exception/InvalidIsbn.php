<?php

declare(strict_types=1);

namespace App\Domain\Exception;

final class InvalidIsbn extends DomainRuleViolation
{
    public static function because(string $value): self
    {
        return new self('El ISBN debe tener exactamente 13 dígitos.');
    }
}