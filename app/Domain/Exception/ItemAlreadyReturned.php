<?php

declare(strict_types=1);

namespace App\Domain\Exception;

final class ItemAlreadyReturned extends DomainRuleViolation
{
    public static function forItem(string $itemId): self
    {
        return new self('Este libro del préstamo ya fue devuelto.');
    }
}