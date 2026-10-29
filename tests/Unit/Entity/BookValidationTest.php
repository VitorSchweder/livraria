<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Author;
use App\Entity\Book;
use App\Entity\Subject;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class BookValidationTest extends TestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        $this->validator = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator();
    }

    public function testCompleteBookIsValid(): void
    {
        self::assertCount(0, $this->validator->validate($this->validBook()));
    }

    /**
     * @return iterable<string, array{string, \Closure(Book): mixed}>
     */
    public static function invalidCases(): iterable
    {
        yield 'blank title' => ['title', static fn (Book $b) => $b->setTitle('  ')];
        yield 'title longer than 40 chars' => ['title', static fn (Book $b) => $b->setTitle(str_repeat('a', 41))];
        yield 'blank publisher' => ['publisher', static fn (Book $b) => $b->setPublisher('')];
        yield 'zero edition' => ['edition', static fn (Book $b) => $b->setEdition(0)];
        yield 'two-digit year' => ['publicationYear', static fn (Book $b) => $b->setPublicationYear('99')];
        yield 'year with letters' => ['publicationYear', static fn (Book $b) => $b->setPublicationYear('20a4')];
        yield 'year far in the future' => ['publicationYear', static fn (Book $b) => $b->setPublicationYear('9999')];
        yield 'year before the printing press' => ['publicationYear', static fn (Book $b) => $b->setPublicationYear('1200')];
        yield 'negative price' => ['price', static fn (Book $b) => $b->setPrice('-1.00')];
        yield 'missing price' => ['price', static fn (Book $b) => $b->setPrice(null)];
        yield 'no authors' => ['authors', static fn (Book $b) => $b->getAuthors()->clear()];
        yield 'no subjects' => ['subjects', static fn (Book $b) => $b->getSubjects()->clear()];
    }

    #[DataProvider('invalidCases')]
    public function testRejectsInvalidData(string $field, \Closure $change): void
    {
        $book = $this->validBook();
        $change($book);

        $violations = $this->validator->validate($book);

        self::assertGreaterThan(0, \count($violations));
        self::assertSame($field, $violations->get(0)->getPropertyPath());
    }

    public function testAddAuthorDoesNotDuplicate(): void
    {
        $author = new Author('Kent Beck');
        $book = (new Book())->addAuthor($author)->addAuthor($author);

        self::assertCount(1, $book->getAuthors());
    }

    public function testSettersTrimWhitespace(): void
    {
        $book = (new Book())->setTitle('  Clean Code  ')->setPublisher(' Prentice Hall ');

        self::assertSame('Clean Code', $book->getTitle());
        self::assertSame('Prentice Hall', $book->getPublisher());
    }

    private function validBook(): Book
    {
        return (new Book())
            ->setTitle('Clean Code')
            ->setPublisher('Prentice Hall')
            ->setEdition(1)
            ->setPublicationYear('2008')
            ->setPrice('189.90')
            ->addAuthor(new Author('Robert C. Martin'))
            ->addSubject(new Subject('Eng. de Software'));
    }
}
