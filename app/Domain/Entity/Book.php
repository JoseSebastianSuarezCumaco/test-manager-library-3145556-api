<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use App\Domain\Exception\BookNotAvailable;
use App\Domain\Exception\InvalidStock;
use App\Domain\ValueObject\Isbn;
use App\Domain\ValueObject\Quantity;
use DateTimeImmutable;

final class Book
{
    private function __construct(
        private readonly string $id,
        private string $title,
        private string $author,
        private readonly Isbn $isbn,
        private string $categoryId,
        private Quantity $total,
        private int $available,
        private ?DateTimeImmutable $deletedAt,
    ) {
    }

    /** New book: available copies start equal to the total (CA-03.1). */
    public static function create(
        string $id,
        string $title,
        string $author,
        Isbn $isbn,
        string $categoryId,
        Quantity $total,
    ): self {
        return new self($id, $title, $author, $isbn, $categoryId, $total, $total->value(), null);
    }

    /** Rebuilds a book from storage; RN-1 is checked again. */
    public static function reconstitute(
        string $id,
        string $title,
        string $author,
        Isbn $isbn,
        string $categoryId,
        Quantity $total,
        int $available,
        ?DateTimeImmutable $deletedAt,
    ): self {
        if ($available < 0 || $available > $total->value()) {
            throw InvalidStock::outOfRange();
        }

        return new self($id, $title, $author, $isbn, $categoryId, $total, $available, $deletedAt);
    }

    /** Takes one copy out for a loan. Never goes below zero. */
    public function withdraw(): void
    {
        if ($this->available < 1) {
            throw BookNotAvailable::forBook($this->id);
        }

        $this->available--;
    }

    /** Puts one copy back after a return. Never goes above the total. */
    public function restock(): void
    {
        if ($this->available >= $this->total->value()) {
            throw InvalidStock::outOfRange();
        }

        $this->available++;
    }

    /** Changes the total and moves available by the same difference (CA-03.3). */
    public function changeTotal(Quantity $newTotal): void
    {
        $newAvailable = $this->available + ($newTotal->value() - $this->total->value());

        if ($newAvailable < 0) {
            throw InvalidStock::availableBelowZero();
        }

        $this->total = $newTotal;
        $this->available = $newAvailable;
    }

    /** Soft delete: the book stays in storage and in past loans (CA-03.5). */
    public function softDelete(DateTimeImmutable $at): void
    {
        if ($this->deletedAt === null) {
            $this->deletedAt = $at;
        }
    }

    public function isActive(): bool
    {
        return $this->deletedAt === null;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function author(): string
    {
        return $this->author;
    }

    public function isbn(): Isbn
    {
        return $this->isbn;
    }

    public function categoryId(): string
    {
        return $this->categoryId;
    }

    public function total(): Quantity
    {
        return $this->total;
    }

    public function available(): int
    {
        return $this->available;
    }

    public function deletedAt(): ?DateTimeImmutable
    {
        return $this->deletedAt;
    }
}