<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Entity;

use App\Domain\Entity\Book;
use App\Domain\Exception\BookNotAvailable;
use App\Domain\Exception\InvalidStock;
use App\Domain\ValueObject\Isbn;
use App\Domain\ValueObject\Quantity;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class BookTest extends TestCase
{
    private function makeBook(int $total = 2): Book
    {
        return Book::create(
            '11111111-1111-4111-8111-111111111111',
            'Clean Code',
            'Robert C. Martin',
            Isbn::fromString('9780132350884'),
            '33333333-3333-4333-8333-333333333333',
            Quantity::of($total),
        );
    }

    public function test_new_book_starts_with_available_equal_to_total(): void
    {
        $book = $this->makeBook(5);

        $this->assertSame(5, $book->available());
        $this->assertTrue($book->isActive());
    }

    public function test_withdraw_decreases_available_by_one(): void
    {
        $book = $this->makeBook(2);

        $book->withdraw();

        $this->assertSame(1, $book->available());
    }

    public function test_withdraw_never_goes_below_zero(): void
    {
        $book = $this->makeBook(1);
        $book->withdraw();

        $this->expectException(BookNotAvailable::class);

        $book->withdraw();
    }

    public function test_restock_increases_available_by_one(): void
    {
        $book = $this->makeBook(2);
        $book->withdraw();

        $book->restock();

        $this->assertSame(2, $book->available());
    }

    public function test_restock_never_goes_above_total(): void
    {
        $book = $this->makeBook(2);

        $this->expectException(InvalidStock::class);

        $book->restock();
    }

    public function test_change_total_moves_available_by_the_same_difference(): void
    {
        $book = $this->makeBook(5);
        $book->withdraw();
        $book->withdraw();

        $book->changeTotal(Quantity::of(8));

        $this->assertSame(8, $book->total()->value());
        $this->assertSame(6, $book->available());
    }

    public function test_change_total_never_leaves_available_below_zero(): void
    {
        $book = $this->makeBook(5);
        $book->withdraw();
        $book->withdraw();
        $book->withdraw();

        try {
            $book->changeTotal(Quantity::of(2));
            $this->fail('Expected InvalidStock');
        } catch (InvalidStock) {
            $this->assertSame(5, $book->total()->value());
            $this->assertSame(2, $book->available());
        }
    }

    public function test_soft_delete_marks_book_as_inactive(): void
    {
        $book = $this->makeBook();
        $at = new DateTimeImmutable('2026-10-13T12:00:00Z');

        $book->softDelete($at);

        $this->assertFalse($book->isActive());
        $this->assertSame($at, $book->deletedAt());
    }

    public function test_reconstitute_rejects_available_out_of_range(): void
    {
        $this->expectException(InvalidStock::class);

        Book::reconstitute(
            '11111111-1111-4111-8111-111111111111',
            'Clean Code',
            'Robert C. Martin',
            Isbn::fromString('9780132350884'),
            '33333333-3333-4333-8333-333333333333',
            Quantity::of(2),
            3,
            null,
        );
    }
}