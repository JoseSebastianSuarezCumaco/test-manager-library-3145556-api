<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\ValueObject;

use App\Domain\Exception\InvalidDateRange;
use App\Domain\Exception\InvalidIsbn;
use App\Domain\Exception\InvalidQuantity;
use App\Domain\ValueObject\DateRange;
use App\Domain\ValueObject\Isbn;
use App\Domain\ValueObject\Quantity;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class ValueObjectsTest extends TestCase
{
    public function test_isbn_accepts_exactly_13_digits(): void
    {
        $isbn = Isbn::fromString('9780306406157');

        $this->assertSame('9780306406157', $isbn->value());
    }

    public function test_isbn_rejects_wrong_length_or_non_digits(): void
    {
        foreach (['', '123', '978030640615', '97803064061577', '978-0306406157', '978030640615X'] as $bad) {
            try {
                Isbn::fromString($bad);
                $this->fail("ISBN '$bad' should have been rejected");
            } catch (InvalidIsbn) {
                $this->addToAssertionCount(1);
            }
        }
    }

    public function test_isbn_equality_is_by_value(): void
    {
        $this->assertTrue(Isbn::fromString('9780306406157')->equals(Isbn::fromString('9780306406157')));
        $this->assertFalse(Isbn::fromString('9780306406157')->equals(Isbn::fromString('9780306406158')));
    }

    public function test_quantity_accepts_positive_numbers(): void
    {
        $this->assertSame(1, Quantity::of(1)->value());
        $this->assertSame(25, Quantity::of(25)->value());
    }

    public function test_quantity_rejects_zero_and_negative(): void
    {
        foreach ([0, -1, -50] as $bad) {
            try {
                Quantity::of($bad);
                $this->fail("Quantity $bad should have been rejected");
            } catch (InvalidQuantity) {
                $this->addToAssertionCount(1);
            }
        }
    }

    public function test_date_range_accepts_end_after_or_equal_to_start(): void
    {
        $start = new DateTimeImmutable('2026-10-01T00:00:00Z');

        $this->assertSame($start, DateRange::between($start, new DateTimeImmutable('2026-10-31T00:00:00Z'))->start());
        $this->assertSame($start, DateRange::between($start, $start)->end());
    }

    public function test_date_range_rejects_end_before_start(): void
    {
        $this->expectException(InvalidDateRange::class);

        DateRange::between(
            new DateTimeImmutable('2026-10-31T00:00:00Z'),
            new DateTimeImmutable('2026-10-01T00:00:00Z'),
        );
    }
}