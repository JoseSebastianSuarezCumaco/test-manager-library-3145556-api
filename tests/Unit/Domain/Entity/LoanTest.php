<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Entity;

use App\Domain\Entity\Loan;
use App\Domain\Exception\DuplicateBookInLoan;
use App\Domain\Exception\ItemAlreadyReturned;
use App\Domain\Exception\LoanItemLimitExceeded;
use App\Domain\Exception\LoanItemNotFound;
use App\Domain\ValueObject\Isbn;
use App\Domain\ValueObject\LoanStatus;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Tests\Support\FixedClock;

final class LoanTest extends TestCase
{
    private DateTimeImmutable $loanedAt;

    protected function setUp(): void
    {
        $this->loanedAt = new DateTimeImmutable('2026-10-01T10:00:00Z');
    }

    private function loanWithItems(int $count = 1): Loan
    {
        $loan = Loan::open(
            'aaaaaaaa-aaaa-4aaa-8aaa-aaaaaaaaaaaa',
            'bbbbbbbb-bbbb-4bbb-8bbb-bbbbbbbbbbbb',
            'cccccccc-cccc-4ccc-8ccc-cccccccccccc',
            $this->loanedAt,
        );

        for ($i = 1; $i <= $count; $i++) {
            $loan->addItem(
                "item-$i",
                "book-$i",
                "Title $i",
                Isbn::fromString('9780132350884'),
                'Tecnología',
            );
        }

        return $loan;
    }

    public function test_item_due_date_is_14_days_after_loaned_at(): void
    {
        $loan = $this->loanWithItems();

        $this->assertEquals(new DateTimeImmutable('2026-10-15T10:00:00Z'), $loan->items()[0]->dueAt());
    }

    public function test_item_freezes_title_isbn_and_category_name(): void
    {
        $item = $this->loanWithItems()->items()[0];

        $this->assertSame('Title 1', $item->title());
        $this->assertSame('9780132350884', $item->isbn()->value());
        $this->assertSame('Tecnología', $item->categoryName());
    }

    public function test_rejects_same_book_twice(): void
    {
        $loan = $this->loanWithItems();

        $this->expectException(DuplicateBookInLoan::class);

        $loan->addItem('item-x', 'book-1', 'Title 1', Isbn::fromString('9780132350884'), 'Tecnología');
    }

    public function test_rejects_fourth_item(): void
    {
        $loan = $this->loanWithItems(3);

        $this->expectException(LoanItemLimitExceeded::class);

        $loan->addItem('item-4', 'book-4', 'Title 4', Isbn::fromString('9780132350884'), 'Tecnología');
    }

    public function test_status_is_open_before_due_date(): void
    {
        $loan = $this->loanWithItems();
        $clock = new FixedClock(new DateTimeImmutable('2026-10-10T10:00:00Z'));

        $this->assertSame(LoanStatus::Open, $loan->status($clock));
    }

    public function test_status_is_open_exactly_at_due_date(): void
    {
        $loan = $this->loanWithItems();
        $clock = new FixedClock(new DateTimeImmutable('2026-10-15T10:00:00Z'));

        $this->assertSame(LoanStatus::Open, $loan->status($clock));
    }

    public function test_status_is_overdue_after_due_date_with_unreturned_item(): void
    {
        $loan = $this->loanWithItems();
        $clock = new FixedClock(new DateTimeImmutable('2026-10-15T10:00:01Z'));

        $this->assertSame(LoanStatus::Overdue, $loan->status($clock));
    }

    public function test_status_is_closed_when_all_items_returned(): void
    {
        $loan = $this->loanWithItems(2);
        $clock = new FixedClock(new DateTimeImmutable('2026-10-20T10:00:00Z'));

        $loan->returnItem('item-1', $clock);
        $loan->returnItem('item-2', $clock);

        $this->assertSame(LoanStatus::Closed, $loan->status($clock));
    }

    public function test_status_is_not_closed_while_one_item_is_pending(): void
    {
        $loan = $this->loanWithItems(2);
        $clock = new FixedClock(new DateTimeImmutable('2026-10-05T10:00:00Z'));

        $loan->returnItem('item-1', $clock);

        $this->assertSame(LoanStatus::Open, $loan->status($clock));
    }

    public function test_return_item_sets_returned_at_from_clock(): void
    {
        $loan = $this->loanWithItems();
        $returnTime = new DateTimeImmutable('2026-10-05T15:30:00Z');

        $loan->returnItem('item-1', new FixedClock($returnTime));

        $this->assertEquals($returnTime, $loan->items()[0]->returnedAt());
    }

    public function test_return_item_twice_is_rejected(): void
    {
        $loan = $this->loanWithItems();
        $clock = new FixedClock(new DateTimeImmutable('2026-10-05T15:30:00Z'));
        $loan->returnItem('item-1', $clock);

        $this->expectException(ItemAlreadyReturned::class);

        $loan->returnItem('item-1', $clock);
    }

    public function test_return_unknown_item_is_rejected(): void
    {
        $loan = $this->loanWithItems();

        $this->expectException(LoanItemNotFound::class);

        $loan->returnItem('does-not-exist', new FixedClock($this->loanedAt));
    }
}