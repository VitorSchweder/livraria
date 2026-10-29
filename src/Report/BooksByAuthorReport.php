<?php

declare(strict_types=1);

namespace App\Report;

final readonly class BooksByAuthorReport
{
    /**
     * @param list<AuthorGroup> $groups
     */
    public function __construct(
        public array $groups,
        public int $distinctBooks,
        public float $collectionValue,
        public \DateTimeImmutable $generatedAt,
    ) {
    }

    public function authorCount(): int
    {
        return \count($this->groups);
    }

    public function isEmpty(): bool
    {
        return $this->groups === [];
    }
}
