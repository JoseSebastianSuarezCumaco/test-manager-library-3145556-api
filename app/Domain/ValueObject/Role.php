<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

use App\Domain\Exception\InvalidRole;

enum Role: string
{
    case Admin = 'admin';
    case Librarian = 'librarian';

    public static function parse(string $value): self
    {
        return self::tryFrom($value) ?? throw InvalidRole::unknown($value);
    }
}