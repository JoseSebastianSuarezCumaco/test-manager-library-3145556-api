<?php

declare(strict_types=1);

namespace App\Domain\Exception;

final class InvalidRole extends DomainRuleViolation
{
    public static function unknown(string $value): self
    {
        return new self('El rol indicado no es válido.');
    }
}