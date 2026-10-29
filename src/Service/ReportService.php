<?php

declare(strict_types=1);

namespace App\Service;

use App\Report\AuthorGroup;
use App\Report\BooksByAuthorReport;
use App\Repository\ReportRepositoryInterface;
use Psr\Clock\ClockInterface;

final readonly class ReportService
{
    public function __construct(
        private ReportRepositoryInterface $repository,
        private ClockInterface $clock,
    ) {
    }

    public function booksByAuthor(?int $authorId = null): BooksByAuthorReport
    {
        /** @var array<int, AuthorGroup> $groups */
        $groups = [];
        /** @var array<int, int> $priceByBook */
        $priceByBook = [];

        foreach ($this->repository->booksByAuthor($authorId) as $row) {
            $groups[$row->authorId] ??= new AuthorGroup($row->authorId, $row->authorName);
            $groups[$row->authorId]->add($row);

            if (!empty($row->bookId)) {
                $priceByBook[$row->bookId] = $row->priceInCents();
            }
        }

        return new BooksByAuthorReport(
            groups: array_values($groups),
            distinctBooks: \count($priceByBook),
            collectionValue: array_sum($priceByBook) / 100,
            generatedAt: $this->clock->now(),
        );
    }
}
