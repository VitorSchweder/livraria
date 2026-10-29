<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\Report\BookByAuthorRow;
use App\Repository\ReportRepository;
use App\Tests\Support\CreatesRecords;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class ReportRepositoryTest extends KernelTestCase
{
    use CreatesRecords;

    private ReportRepository $repository;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->repository = static::getContainer()->get(ReportRepository::class);
    }

    public function testBookWithTwoAuthorsAppearsUnderEachAuthor(): void
    {
        $beck = $this->createAuthor('Kent Beck');
        $fowler = $this->createAuthor('Martin Fowler');
        $book = $this->createBook('Refactoring', [$beck, $fowler], [$this->createSubject('Testes'), $this->createSubject('Arquitetura')], '249.90');

        $rows = array_values(array_filter(
            $this->repository->booksByAuthor(),
            static fn (BookByAuthorRow $row): bool => $row->bookId === $book->getId(),
        ));

        self::assertCount(2, $rows);
        self::assertSame(['Kent Beck', 'Martin Fowler'], array_map(static fn (BookByAuthorRow $row): string => $row->authorName, $rows));
        self::assertSame(['Martin Fowler'], $rows[0]->coAuthors);
        self::assertSame(['Kent Beck'], $rows[1]->coAuthors);
        self::assertSame(['Arquitetura', 'Testes'], $rows[0]->subjects);
        self::assertSame('249.90', $rows[0]->price);
    }

    public function testAuthorWithoutBooksAppearsInView(): void
    {
        $author = $this->createAuthor('Author Without Books');

        $rows = $this->repository->booksByAuthor($author->getId());

        self::assertCount(1, $rows);
        self::assertFalse($rows[0]->hasBook());
    }

    public function testFiltersByAuthor(): void
    {
        $beck = $this->createAuthor('Kent Beck');
        $evans = $this->createAuthor('Eric Evans');
        $subject = $this->createSubject('Arquitetura');
        $this->createBook('TDD', [$beck], [$subject]);
        $this->createBook('DDD', [$evans], [$subject]);

        $rows = $this->repository->booksByAuthor($evans->getId());

        self::assertSame(['DDD'], array_map(static fn (BookByAuthorRow $row): ?string => $row->title, $rows));
    }
}
