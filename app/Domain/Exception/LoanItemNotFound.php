<?php

declare(strict_types=1);

namespace App\Domain\Exception;

final class LoanItemNotFound extends DomainRuleViolation
{
    public static function forItem(string $itemId): self
    {
        return new self('La línea indicada no pertenece a este préstamo.');
    }
}