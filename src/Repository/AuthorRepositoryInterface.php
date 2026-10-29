<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Author;
use App\Pagination\Page;

interface AuthorRepositoryInterface
{
    public function findById(int $id): ?Author;

    /** @return Page<Author> */
    public function paginate(?string $search, int $page): Page;

    /** @return list<Author> */
    public function findAllOrderedByName(): array;

    public function countAll(): int;

    public function save(Author $author): void;

    public function delete(Author $author): void;
}
