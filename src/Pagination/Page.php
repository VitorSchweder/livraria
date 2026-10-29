<?php

declare(strict_types=1);

namespace App\Pagination;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * @template T
 */
final readonly class Page
{
    public const int ITEMS_PER_PAGE = 10;

    /**
     * @param list<T> $items
     */
    public function __construct(
        public array $items,
        public int $currentPage,
        public int $itemsPerPage,
        public int $total,
    ) {
    }

    /**
     * @param QueryBuilder|Query<mixed, mixed> $query
     *
     * @return self<mixed>
     */
    public static function fromQuery(QueryBuilder|Query $query, int $page, int $itemsPerPage = self::ITEMS_PER_PAGE, bool $fetchJoinCollection = false): self
    {
        $page = max(1, $page);

        $query = $query instanceof QueryBuilder ? $query->getQuery() : $query;
        $query->setFirstResult(($page - 1) * $itemsPerPage)
            ->setMaxResults($itemsPerPage);

        $paginator = new Paginator($query, $fetchJoinCollection);

        return new self(
            array_values(iterator_to_array($paginator)),
            $page,
            $itemsPerPage,
            \count($paginator),
        );
    }

    public function totalPages(): int
    {
        return max(1, (int) ceil($this->total / $this->itemsPerPage));
    }

    public function hasPreviousPage(): bool
    {
        return $this->currentPage > 1;
    }

    public function hasNextPage(): bool
    {
        return $this->currentPage < $this->totalPages();
    }
}
