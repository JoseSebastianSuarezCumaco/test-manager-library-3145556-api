<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

/** Calculated state of a loan; it is never stored (CA-08.3). */
enum LoanStatus: string
{
    case Open = 'open';
    case Overdue = 'overdue';
    case Closed = 'closed';
}