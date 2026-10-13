<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

use App\Domain\Exception\InvalidDateRange;
use DateTimeImmutable;

final class DateRange
{
    private function __construct(
        private readonly DateTimeImmutable $start,
        private readonly DateTimeImmutable $end,
    ) {
    }

    public static function between(DateTimeImmutable $start, DateTimeImmutable $end): self
    {
        if ($end < $start) {
            throw InvalidDateRange::endBeforeStart();
        }

        return new self($start, $end);
    }

    public function start(): DateTimeImmutable
    {
        return $this->start;
    }

    public function end(): DateTimeImmutable
    {
        return $this->end;
    }
}