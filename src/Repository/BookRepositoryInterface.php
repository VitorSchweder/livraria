<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Book;
use App\Pagination\Page;

interface BookRepositoryInterface
{
    public function findById(int $id): ?Book;

    /** @return Page<Book> */
    public function paginate(?string $search, int $page): Page;

    public function countAll(): int;

    public function save(Book $book): void;

    public function delete(Book $book): void;
}
