<?php

declare(strict_types=1);

namespace App\Domain\Exception;

final class LoanItemLimitExceeded extends DomainRuleViolation
{
    public static function max(int $max): self
    {
        return new self("Un préstamo no puede tener más de $max libros.");
    }
}