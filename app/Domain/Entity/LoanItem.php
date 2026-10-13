<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use App\Domain\Exception\ItemAlreadyReturned;
use App\Domain\ValueObject\Isbn;
use DateTimeImmutable;

/**
 * One line of a loan. Title, ISBN and category name are frozen at the moment
 * of the loan and never follow the catalog afterwards (RN-4).
 */
final class LoanItem
{
    private function __construct(
        private readonly string $id,
        private readonly string $bookId,
        private readonly string $title,
        private readonly Isbn $isbn,
        private readonly string $categoryName,
        private readonly DateTimeImmutable $dueAt,
        private ?DateTimeImmutable $returnedAt,
    ) {
    }

    public static function freeze(
        string $id,
        string $bookId,
        string $title,
        Isbn $isbn,
        string $categoryName,
        DateTimeImmutable $dueAt,
    ): self {
        return new self($id, $bookId, $title, $isbn, $categoryName, $dueAt, null);
    }

    public static function reconstitute(
        string $id,
        string $bookId,
        string $title,
        Isbn $isbn,
        string $categoryName,
        DateTimeImmutable $dueAt,
        ?DateTimeImmutable $returnedAt,
    ): self {
        return new self($id, $bookId, $title, $isbn, $categoryName, $dueAt, $returnedAt);
    }

    /** A line can be returned only once (CA-07.2). */
    public function markReturned(DateTimeImmutable $at): void
    {
        if ($this->returnedAt !== null) {
            throw ItemAlreadyReturned::forItem($this->id);
        }

        $this->returnedAt = $at;
    }

    public function isReturned(): bool
    {
        return $this->returnedAt !== null;
    }

    /** Overdue means not returned and due_at strictly before now (CA-09.1). */
    public function isOverdueAt(DateTimeImmutable $now): bool
    {
        return $this->returnedAt === null && $this->dueAt < $now;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function bookId(): string
    {
        return $this->bookId;
    }

    public function title(): string
    {
        return $this->title;
    }

    public function isbn(): Isbn
    {
        return $this->isbn;
    }

    public function categoryName(): string
    {
        return $this->categoryName;
    }

    public function dueAt(): DateTimeImmutable
    {
        return $this->dueAt;
    }

    public function returnedAt(): ?DateTimeImmutable
    {
        return $this->returnedAt;
    }
}