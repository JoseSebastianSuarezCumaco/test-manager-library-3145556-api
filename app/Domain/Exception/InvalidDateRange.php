<?php

declare(strict_types=1);

namespace App\Domain\Exception;

final class InvalidDateRange extends DomainRuleViolation
{
    public static function endBeforeStart(): self
    {
        return new self('La fecha final no puede ser anterior a la inicial.');
    }
}