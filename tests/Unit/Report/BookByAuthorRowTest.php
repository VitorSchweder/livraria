<?php

declare(strict_types=1);

namespace App\Tests\Unit\Report;

use App\Report\BookByAuthorRow;
use PHPUnit\Framework\TestCase;

final class BookByAuthorRowTest extends TestCase
{
    public function testMapsViewRow(): void
    {
        $row = BookByAuthorRow::fromView([
            'autor_codau' => 3,
            'autor_nome' => 'Kent Beck',
            'livro_codl' => 10,
            'titulo' => 'Refactoring',
            'editora' => 'Addison-Wesley',
            'edicao' => 2,
            'anopublicacao' => '2018',
            'valor' => '249.90',
            'assuntos' => 'Eng. de Software|Testes',
            'coautores' => 'Martin Fowler',
        ]);

        self::assertSame(3, $row->authorId);
        self::assertSame(10, $row->bookId);
        self::assertSame(['Eng. de Software', 'Testes'], $row->subjects);
        self::assertSame(['Martin Fowler'], $row->coAuthors);
        self::assertSame(24990, $row->priceInCents());
        self::assertTrue($row->hasBook());
    }

    public function testAuthorWithoutBookHasNullColumns(): void
    {
        $row = BookByAuthorRow::fromView([
            'autor_codau' => 5,
            'autor_nome' => 'New Author',
            'livro_codl' => null,
            'titulo' => null,
            'editora' => null,
            'edicao' => null,
            'anopublicacao' => null,
            'valor' => null,
            'assuntos' => null,
            'coautores' => null,
        ]);

        self::assertFalse($row->hasBook());
        self::assertSame([], $row->subjects);
        self::assertSame([], $row->coAuthors);
        self::assertSame(0, $row->priceInCents());
    }
}
