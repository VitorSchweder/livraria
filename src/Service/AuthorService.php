<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Author;
use App\Exception\RecordInUseException;
use App\Exception\RecordNotFoundException;
use App\Pagination\Page;
use App\Repository\AuthorRepositoryInterface;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Psr\Log\LoggerInterface;

final readonly class AuthorService
{
    public function __construct(
        private AuthorRepositoryInterface $repository,
        private LoggerInterface $logger,
    ) {
    }

    /** @return Page<Author> */
    public function list(?string $search, int $page): Page
    {
        return $this->repository->paginate($search, $page);
    }

    public function get(int $id): Author
    {
        return $this->repository->findById($id)
            ?? throw RecordNotFoundException::for('Autor', $id);
    }

    /** @return list<Author> */
    public function all(): array
    {
        return $this->repository->findAllOrderedByName();
    }

    public function count(): int
    {
        return $this->repository->countAll();
    }

    public function save(Author $author): void
    {
        $this->repository->save($author);
    }

    public function delete(Author $author): void
    {
        try {
            $this->repository->delete($author);
        } catch (ForeignKeyConstraintViolationException $e) {
            $this->logger->info('Author deletion blocked: linked to books.', [
                'author_id' => $author->getId(),
                'sqlstate' => $e->getSQLState(),
            ]);

            throw RecordInUseException::linkedToBooks('autor', (string) $author->getName(), $e);
        }
    }
}
