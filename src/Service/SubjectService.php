<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Subject;
use App\Exception\DuplicateRecordException;
use App\Exception\RecordInUseException;
use App\Exception\RecordNotFoundException;
use App\Pagination\Page;
use App\Repository\SubjectRepositoryInterface;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Psr\Log\LoggerInterface;

final readonly class SubjectService
{
    public function __construct(
        private SubjectRepositoryInterface $repository,
        private LoggerInterface $logger,
    ) {
    }

    /** @return Page<Subject> */
    public function list(?string $search, int $page): Page
    {
        return $this->repository->paginate($search, $page);
    }

    public function get(int $id): Subject
    {
        return $this->repository->findById($id)
            ?? throw RecordNotFoundException::for('Assunto', $id);
    }

    public function count(): int
    {
        return $this->repository->countAll();
    }

    public function save(Subject $subject): void
    {
        try {
            $this->repository->save($subject);
        } catch (UniqueConstraintViolationException $e) {
            $this->logger->info('Duplicate subject rejected by unique constraint.', [
                'description' => $subject->getDescription(),
                'sqlstate' => $e->getSQLState(),
            ]);

            throw DuplicateRecordException::for('assunto', (string) $subject->getDescription(), $e);
        }
    }

    public function delete(Subject $subject): void
    {
        try {
            $this->repository->delete($subject);
        } catch (ForeignKeyConstraintViolationException $e) {
            $this->logger->info('Subject deletion blocked: linked to books.', [
                'subject_id' => $subject->getId(),
                'sqlstate' => $e->getSQLState(),
            ]);

            throw RecordInUseException::linkedToBooks('assunto', (string) $subject->getDescription(), $e);
        }
    }
}
