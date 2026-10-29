<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service;

use App\Report\AuthorGroup;
use App\Report\BookByAuthorRow;
use App\Repository\ReportRepositoryInterface;
use App\Service\ReportService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Clock\MockClock;

final class ReportServiceTest extends TestCase
{
    public function testGroupsRowsByAuthorKeepingViewOrder(): void
    {
        $report = $this->service([
            $this->row(1, 'Kent Beck', 10, 'Refactoring', '249.90', ['Martin Fowler']),
            $this->row(1, 'Kent Beck', 11, 'TDD', '199.00'),
            $this->row(2, 'Martin Fowler', 10, 'Refactoring', '249.90', ['Kent Beck']),
        ])->booksByAuthor();

        self::assertSame(2, $report->authorCount());
        self::assertSame(['Kent Beck', 'Martin Fowler'], array_map(static fn (AuthorGroup $g): string => $g->name, $report->groups));
        self::assertSame(2, $report->groups[0]->bookCount());
        self::assertEqualsWithDelta(448.90, $report->groups[0]->totalPrice(), 0.001);
        self::assertSame(['Kent Beck'], $report->groups[1]->books()[0]->coAuthors);
    }

    public function testBookWithSeveralAuthorsCountsOnceInCollectionValue(): void
    {
        $report = $this->service([
            $this->row(1, 'Kent Beck', 10, 'Refactoring', '249.90'),
            $this->row(2, 'Martin Fowler', 10, 'Refactoring', '249.90'),
        ])->booksByAuthor();

        self::assertSame(1, $report->distinctBooks);
        self::assertEqualsWithDelta(249.90, $report->collectionValue, 0.001);
    }

    public function testAuthorWithoutBooksAppearsWithEmptyGroup(): void
    {
        $report = $this->service([
            $this->row(3, 'New Author', null, null, null),
        ])->booksByAuthor();

        self::assertSame(1, $report->authorCount());
        self::assertSame(0, $report->groups[0]->bookCount());
        self::assertSame(0, $report->distinctBooks);
    }

    public function testSumsInCentsToAvoidRoundingErrors(): void
    {
        $report = $this->service([
            $this->row(1, 'A', 1, 'B1', '0.10'),
            $this->row(1, 'A', 2, 'B2', '0.20'),
        ])->booksByAuthor();

        self::assertSame(0.3, $report->groups[0]->totalPrice());
    }

    public function testRecordsGenerationTime(): void
    {
        $report = $this->service([], '2026-10-28 14:30:00')->booksByAuthor();

        self::assertTrue($report->isEmpty());
        self::assertSame('2026-10-28 14:30', $report->generatedAt->format('Y-m-d H:i'));
    }

    public function testPassesAuthorFilterToRepository(): void
    {
        $repository = $this->createMock(ReportRepositoryInterface::class);
        $repository->expects(self::once())->method('booksByAuthor')->with(7)->willReturn([]);

        new ReportService($repository, new MockClock())->booksByAuthor(7);
    }

    /**
     * @param list<BookByAuthorRow> $rows
     */
    private function service(array $rows, string $now = 'now'): ReportService
    {
        $repository = $this->createStub(ReportRepositoryInterface::class);
        $repository->method('booksByAuthor')->willReturn($rows);

        return new ReportService($repository, new MockClock($now));
    }

    /**
     * @param list<string> $coAuthors
     */
    private function row(int $authorId, string $author, ?int $bookId, ?string $title, ?string $price, array $coAuthors = []): BookByAuthorRow
    {
        return new BookByAuthorRow($authorId, $author, $bookId, $title, empty($bookId) ? null : 'Publisher', empty($bookId) ? null : 1, empty($bookId) ? null : '2000', $price, [], $coAuthors);
    }
}
