<?php

declare(strict_types=1);

namespace App\Tests\Integration\Service;

use App\Entity\Subject;
use App\Exception\DuplicateRecordException;
use App\Exception\RecordInUseException;
use App\Service\AuthorService;
use App\Service\BookService;
use App\Service\SubjectService;
use App\Tests\Support\CreatesRecords;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\DriverException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class DatabaseConstraintsTest extends KernelTestCase
{
    use CreatesRecords;

    protected function setUp(): void
    {
        self::bootKernel();
    }

    public function testDoesNotDeleteAuthorLinkedToBook(): void
    {
        $author = $this->createAuthor('Kent Beck');
        $this->createBook('TDD', [$author], [$this->createSubject('Testes')]);
        $authorId = $author->getId();

        try {
            static::getContainer()->get(AuthorService::class)->delete($author);
            self::fail('RecordInUseException was expected.');
        } catch (RecordInUseException $e) {
            self::assertStringContainsString('Kent Beck', $e->getMessage());
        }

        self::assertSame(1, $this->countRows('SELECT COUNT(*) FROM autor WHERE codau = ?', [$authorId]));
    }

    public function testDeletesAuthorWithoutBooks(): void
    {
        $author = $this->createAuthor('Free Author');
        $authorId = $author->getId();

        static::getContainer()->get(AuthorService::class)->delete($author);

        self::assertSame(0, $this->countRows('SELECT COUNT(*) FROM autor WHERE codau = ?', [$authorId]));
    }

    public function testDoesNotDeleteSubjectLinkedToBook(): void
    {
        $subject = $this->createSubject('Romance');
        $this->createBook('Dom Casmurro', [$this->createAuthor('Machado de Assis')], [$subject]);

        $this->expectException(RecordInUseException::class);

        static::getContainer()->get(SubjectService::class)->delete($subject);
    }

    public function testSubjectUniqueConstraintBecomesDuplicateRecord(): void
    {
        $this->createSubject('Romance');

        $this->expectException(DuplicateRecordException::class);

        static::getContainer()->get(SubjectService::class)->save(new Subject('Romance'));
    }

    public function testDeletingBookRemovesLinksAndKeepsAuthorsAndSubjects(): void
    {
        $author = $this->createAuthor('Eric Evans');
        $subject = $this->createSubject('Arquitetura');
        $book = $this->createBook('DDD', [$author], [$subject]);
        $bookId = $book->getId();

        static::getContainer()->get(BookService::class)->delete($book);

        self::assertSame(0, $this->countRows('SELECT COUNT(*) FROM livro_autor WHERE livro_codl = ?', [$bookId]));
        self::assertSame(0, $this->countRows('SELECT COUNT(*) FROM livro_assunto WHERE livro_codl = ?', [$bookId]));
        self::assertSame(1, $this->countRows('SELECT COUNT(*) FROM autor WHERE codau = ?', [$author->getId()]));
        self::assertSame(1, $this->countRows('SELECT COUNT(*) FROM assunto WHERE codas = ?', [$subject->getId()]));
    }

    public function testCheckConstraintRejectsNegativePrice(): void
    {
        $this->expectException(DriverException::class);
        $this->expectExceptionMessage('livro_valor_chk');

        static::getContainer()->get(Connection::class)->executeStatement(
            "INSERT INTO livro (titulo, editora, edicao, anopublicacao, valor) VALUES ('X', 'Y', 1, '2020', -1)",
        );
    }

    /**
     * @param list<mixed> $params
     */
    private function countRows(string $sql, array $params): int
    {
        return (int) static::getContainer()->get(Connection::class)->fetchOne($sql, $params);
    }
}
