<?php

declare(strict_types=1);

namespace App\Domain\Exception;

use RuntimeException;

/**
 * Base class for every business-rule violation raised by the domain.
 * The domain layer never depends on Laravel or on HTTP.
 */
class DomainRuleViolation extends RuntimeException
{
}