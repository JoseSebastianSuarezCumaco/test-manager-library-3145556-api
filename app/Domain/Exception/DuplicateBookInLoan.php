<?php

declare(strict_types=1);

namespace App\Domain\Exception;

final class DuplicateBookInLoan extends DomainRuleViolation
{
    public static function forBook(string $bookId): self
    {
        return new self('No se puede repetir el mismo libro en un préstamo.');
    }
}