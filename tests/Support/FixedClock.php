<?php

declare(strict_types=1);

namespace Tests\Support;

use App\Domain\Clock;
use DateTimeImmutable;

/** Fake clock for tests: time only moves when the test says so. */
final class FixedClock implements Clock
{
    public function __construct(private DateTimeImmutable $now)
    {
    }

    public function setTo(DateTimeImmutable $now): void
    {
        $this->now = $now;
    }

    public function now(): DateTimeImmutable
    {
        return $this->now;
    }
}