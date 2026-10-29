<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Entity\Book;
use App\Exception\InvalidRelationshipException;
use App\Exception\RecordNotFoundException;
use App\Pagination\Page;
use App\Repository\BookRepositoryInterface;
use App\Service\BookService;
use App\Tests\Support\DatabaseExceptions;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

final class BookServiceTest extends TestCase
{
    public function testListPassesSearchAndPage(): void
    {
        $page = new Page([], 2, 10, 0);
        $repository = $this->createMock(BookRepositoryInterface::class);
        $repository->expects(self::once())->method('paginate')->with('clean', 2)->willReturn($page);

        self::assertSame($page, new BookService($repository, new NullLogger())->list('clean', 2));
    }

    public function testGetThrowsNotFoundWhenMissing(): void
    {
        $repository = $this->createStub(BookRepositoryInterface::class);
        $repository->method('findById')->willReturn(null);

        $this->expectException(RecordNotFoundException::class);
        $this->expectExceptionMessage('Livro de código 99 não encontrado.');

        new BookService($repository, new NullLogger())->get(99);
    }

    public function testSaveTranslatesForeignKeyViolationIntoInvalidRelationship(): void
    {
        $repository = $this->createStub(BookRepositoryInterface::class);
        $repository->method('save')->willThrowException(DatabaseExceptions::foreignKey());

        $this->expectException(InvalidRelationshipException::class);

        new BookService($repository, new NullLogger())->save(new Book());
    }

    public function testDeleteDelegatesToRepository(): void
    {
        $book = new Book();
        $repository = $this->createMock(BookRepositoryInterface::class);
        $repository->expects(self::once())->method('delete')->with($book);

        new BookService($repository, new NullLogger())->delete($book);
    }
}
