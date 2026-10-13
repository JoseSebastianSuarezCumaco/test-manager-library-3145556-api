<?php

declare(strict_types=1);

namespace App\Domain\Exception;

final class AdminRoleNotAllowed extends DomainRuleViolation
{
    public static function viaApi(): self
    {
        return new self('El rol admin no se puede asignar por la API.');
    }
}