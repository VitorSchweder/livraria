<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Book;
use App\Exception\InvalidRelationshipException;
use App\Exception\RecordNotFoundException;
use App\Pagination\Page;
use App\Repository\BookRepositoryInterface;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Psr\Log\LoggerInterface;

final readonly class BookService
{
    public function __construct(
        private BookRepositoryInterface $repository,
        private LoggerInterface $logger,
    ) {
    }

    /** @return Page<Book> */
    public function list(?string $search, int $page): Page
    {
        return $this->repository->paginate($search, $page);
    }

    public function get(int $id): Book
    {
        return $this->repository->findById($id)
            ?? throw RecordNotFoundException::for('Livro', $id);
    }

    public function count(): int
    {
        return $this->repository->countAll();
    }

    public function save(Book $book): void
    {
        try {
            $this->repository->save($book);
        } catch (ForeignKeyConstraintViolationException $e) {
            $this->logger->warning('Book references a missing author or subject.', [
                'book_id' => $book->getId(),
                'sqlstate' => $e->getSQLState(),
            ]);

            throw InvalidRelationshipException::authorOrSubjectRemoved($e);
        }
    }

    public function delete(Book $book): void
    {
        $this->repository->delete($book);
    }
}
