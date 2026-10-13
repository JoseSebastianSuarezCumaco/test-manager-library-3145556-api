<?php

declare(strict_types=1);

namespace App\Domain\Entity;

/** A person who borrows books. It is data, not a system user. */
final class Member
{
    private function __construct(
        private readonly string $id,
        private readonly string $name,
        private readonly string $document,
    ) {
    }

    public static function register(string $id, string $name, string $document): self
    {
        return new self($id, $name, $document);
    }

    public static function reconstitute(string $id, string $name, string $document): self
    {
        return new self($id, $name, $document);
    }

    public function id(): string
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function document(): string
    {
        return $this->document;
    }
}