<?php

declare(strict_types=1);

namespace App\Domain\Exception;

final class BookNotAvailable extends DomainRuleViolation
{
    public static function forBook(string $bookId): self
    {
        return new self('No hay ejemplares disponibles de este libro.');
    }
}