<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Entity\Author;
use App\Exception\RecordInUseException;
use App\Exception\RecordNotFoundException;
use App\Repository\AuthorRepositoryInterface;
use App\Service\AuthorService;
use App\Tests\Support\DatabaseExceptions;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

final class AuthorServiceTest extends TestCase
{
    public function testGetReturnsTheAuthorWhenItExists(): void
    {
        $author = new Author('Kent Beck');
        $repository = $this->createStub(AuthorRepositoryInterface::class);
        $repository->method('findById')->willReturn($author);

        self::assertSame($author, new AuthorService($repository, new NullLogger())->get(1));
    }

    public function testGetThrowsNotFoundWhenMissing(): void
    {
        $repository = $this->createStub(AuthorRepositoryInterface::class);
        $repository->method('findById')->willReturn(null);

        $this->expectException(RecordNotFoundException::class);
        $this->expectExceptionMessage('Autor de código 42 não encontrado.');

        new AuthorService($repository, new NullLogger())->get(42);
    }

    public function testSaveDelegatesToRepository(): void
    {
        $author = new Author('Martin Fowler');
        $repository = $this->createMock(AuthorRepositoryInterface::class);
        $repository->expects(self::once())->method('save')->with($author);

        new AuthorService($repository, new NullLogger())->save($author);
    }

    public function testDeleteTranslatesForeignKeyViolationIntoRecordInUse(): void
    {
        $repository = $this->createStub(AuthorRepositoryInterface::class);
        $repository->method('delete')->willThrowException(DatabaseExceptions::foreignKey());

        try {
            new AuthorService($repository, new NullLogger())->delete(new Author('Eric Evans'));
            self::fail('RecordInUseException was expected.');
        } catch (RecordInUseException $e) {
            self::assertStringContainsString('"Eric Evans"', $e->getMessage());
            self::assertInstanceOf(ForeignKeyConstraintViolationException::class, $e->getPrevious());
        }
    }

    public function testDeleteDoesNotHideOtherDatabaseFailures(): void
    {
        $repository = $this->createStub(AuthorRepositoryInterface::class);
        $repository->method('delete')->willThrowException(DatabaseExceptions::unique());

        $this->expectException(UniqueConstraintViolationException::class);

        new AuthorService($repository, new NullLogger())->delete(new Author('Eric Evans'));
    }
}
