<?php

declare(strict_types=1);

namespace App\Report;

final class AuthorGroup
{
    /** @var list<BookByAuthorRow> */
    private array $books = [];

    private int $totalPriceInCents = 0;

    public function __construct(
        public readonly int $authorId,
        public readonly string $name,
    ) {
    }

    public function add(BookByAuthorRow $row): void
    {
        if (!$row->hasBook()) {
            return;
        }

        $this->books[] = $row;
        $this->totalPriceInCents += $row->priceInCents();
    }

    /** @return list<BookByAuthorRow> */
    public function books(): array
    {
        return $this->books;
    }

    public function bookCount(): int
    {
        return \count($this->books);
    }

    public function totalPrice(): float
    {
        return $this->totalPriceInCents / 100;
    }
}
