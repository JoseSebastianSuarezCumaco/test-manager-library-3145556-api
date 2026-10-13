<?php

declare(strict_types=1);

namespace App\Domain\Entity;

use App\Domain\Clock;
use App\Domain\Exception\DuplicateBookInLoan;
use App\Domain\Exception\LoanItemLimitExceeded;
use App\Domain\Exception\LoanItemNotFound;
use App\Domain\ValueObject\Isbn;
use App\Domain\ValueObject\LoanStatus;
use DateInterval;
use DateTimeImmutable;

final class Loan
{
    public const TERM_DAYS = 14;
    public const MAX_ITEMS = 3;

    /** @param list<LoanItem> $items */
    private function __construct(
        private readonly string $id,
        private readonly string $memberId,
        private readonly string $registeredBy,
        private readonly DateTimeImmutable $loanedAt,
        private array $items,
    ) {
    }

    public static function open(
        string $id,
        string $memberId,
        string $registeredBy,
        DateTimeImmutable $loanedAt,
    ): self {
        return new self($id, $memberId, $registeredBy, $loanedAt, []);
    }

    /** @param list<LoanItem> $items */
    public static function reconstitute(
        string $id,
        string $memberId,
        string $registeredBy,
        DateTimeImmutable $loanedAt,
        array $items,
    ): self {
        return new self($id, $memberId, $registeredBy, $loanedAt, $items);
    }

    /** Adds a line: no repeated book (CA-06.2), at most 3 lines, due 14 days after loaned_at (CA-06.6). */
    public function addItem(
        string $itemId,
        string $bookId,
        string $title,
        Isbn $isbn,
        string $categoryName,
    ): void {
        foreach ($this->items as $item) {
            if ($item->bookId() === $bookId) {
                throw DuplicateBookInLoan::forBook($bookId);
            }
        }

        if (count($this->items) >= self::MAX_ITEMS) {
            throw LoanItemLimitExceeded::max(self::MAX_ITEMS);
        }

        $dueAt = $this->loanedAt->add(new DateInterval('P' . self::TERM_DAYS . 'D'));

        $this->items[] = LoanItem::freeze($itemId, $bookId, $title, $isbn, $categoryName, $dueAt);
    }

    /** Returns one line using the clock's current time (CA-07.1). */
    public function returnItem(string $itemId, Clock $clock): void
    {
        foreach ($this->items as $item) {
            if ($item->id() === $itemId) {
                $item->markReturned($clock->now());

                return;
            }
        }

        throw LoanItemNotFound::forItem($itemId);
    }

    /** Calculated, never stored (CA-08.3). */
    public function status(Clock $clock): LoanStatus
    {
        if ($this->items === []) {
            return LoanStatus::Open;
        }

        $now = $clock->now();
        $allReturned = true;
        $overdue = false;

        foreach ($this->items as $item) {
            if (! $item->isReturned()) {
                $allReturned = false;

                if ($item->isOverdueAt($now)) {
                    $overdue = true;
                }
            }
        }

        if ($allReturned) {
            return LoanStatus::Closed;
        }

        return $overdue ? LoanStatus::Overdue : LoanStatus::Open;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function memberId(): string
    {
        return $this->memberId;
    }

    public function registeredBy(): string
    {
        return $this->registeredBy;
    }

    public function loanedAt(): DateTimeImmutable
    {
        return $this->loanedAt;
    }

    /** @return list<LoanItem> */
    public function items(): array
    {
        return $this->items;
    }
}