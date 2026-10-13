<?php

declare(strict_types=1);

namespace App\Domain;

use DateTimeImmutable;

/**
 * Port for "what time is it". The domain never calls new DateTimeImmutable()
 * for business decisions, so tests can freeze the time.
 */
interface Clock
{
    public function now(): DateTimeImmutable;
}